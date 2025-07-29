/**
 * Reads a Blob object and returns its content as a Base64 Data URL.
 * @param {Blob} blob - The Blob object to read.
 * @returns {Promise<string>} A promise that resolves with the Base64 Data URL string.
 * @throws {Error} If the FileReader encounters an error.
 */
function blobToDataURL(blob) {
  return new Promise((resolve, reject) => {
    // Create a new FileReader instance
    const reader = new FileReader();

    // Set up the event handler for when reading is complete
    reader.onloadend = () => {
      // reader.result contains the Data URL string (e.g., "data:image/png;base64,...")
      resolve(reader.result);
    };
    // Set up the event handler for reading errors
    reader.onerror = (error) => {
      console.error("FileReader error:", error);
      reject(new Error("Failed to read blob data using FileReader."));
    };

    // Start reading the Blob object as a Data URL
    reader.readAsDataURL(blob);
  });
}


/**
 * Parses an HTML string, finds all <img> elements with 'blob:' src attributes,
 * converts the corresponding blob data to Base64, uploads the Base64 string
 * to '/saveImage.php' in a JSON payload, updates the src attribute with the
 * URL returned by the API, and returns the modified HTML string.
 *
 * Assumes the '/saveImage.php' endpoint accepts a POST request with a JSON body like:
 * { "imageData": "data:image/png;base64,..." }
 * and returns JSON like:
 * { "url": "/path/to/saved/image.png" } on success.
 *
 * @param {string} htmlString - The input HTML string to process.
 * @returns {Promise<string>} A promise that resolves with the modified HTML string.
 * @throws {Error} If parsing fails or other critical errors occur.
 */
async function uploadImage(htmlString, csrf) {

  // Use DOMParser to safely parse the HTML string into a document
  const parser = new DOMParser();
  const doc = parser.parseFromString(htmlString, 'text/html');

  // Select all img elements whose src attribute starts with "blob:" within the parsed document
  const blobImages = doc.querySelectorAll('img[src^="blob:"]');

  if (blobImages.length === 0) {
    // Return the original string if no changes are needed
    return htmlString;
  }

  // --- Process images concurrently ---
  const uploadPromises = Array.from(blobImages).map(async (imgElement) => {
    const blobUrl = imgElement.getAttribute('src'); // Use getAttribute for parsed elements

    // *** CRUCIAL ASSUMPTION ***
    // We assume the blobUrl is valid in the *current* browser execution context,
    // otherwise the initial fetch() will fail. Blobs are typically context-specific.

    try {
      // 1. Fetch the blob data directly from browser memory
      const response = await fetch(blobUrl);
      if (!response.ok) {
        throw new Error(`Failed to fetch blob data: ${response.status} ${response.statusText}`);
      }
      const blobData = await response.blob();

      // 2. Convert the Blob object to a Base64 Data URL string
      const base64ImageData = await blobToDataURL(blobData);
			imgElement.setAttribute('src', base64ImageData); // Update src with the data URL
		} catch (error) {
			console.error(`Failed to process blob URL ${blobUrl}:`, error);
			// Image src in the parsed doc will remain the original blob URL on error
		}
  }); // End of map function

  // Wait for all upload and update attempts to complete
  await Promise.allSettled(uploadPromises);

  // Serialize the modified document body back into an HTML string
  // Using doc.body.innerHTML assumes the original string represented body content.
  // If the string could be a full HTML doc, use doc.documentElement.outerHTML
  const modifiedHtmlString = doc.body.innerHTML;

  return modifiedHtmlString;
}

