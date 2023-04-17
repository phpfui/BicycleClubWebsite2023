<?php

namespace App\Model;

class Video
	{
	public function getUploadParameters() : string
		{
		$parameters = [];

		$parameters['target'] = '/Video/upload';
//    $parameters['singleFile'] = 'Enable single file upload. Once one file is uploaded, second file will overtake existing one, first one will be canceled. (Default: false)';
//    $parameters['chunkSize'] = 'The size in bytes of each uploaded chunk of data. This can be a number or a function. If a function, it will be passed a FlowFile. The last uploaded chunk will be at least this size and up to two the size, see Issue #51 for details and reasons. (Default: 1*1024*1024, 1MB)';
//    $parameters['forceChunkSize'] = 'Force all chunks to be less or equal than chunkSize. Otherwise, the last chunk will be greater than or equal to chunkSize. (Default: false)';
//    $parameters['simultaneousUploads'] = 'Number of simultaneous uploads (Default: 3)';
//    $parameters['fileParameterName'] = 'The name of the multipart POST parameter to use for the file chunk (Default: file)';
//    $parameters['query'] = 'Extra parameters to include in the multipart POST with data. This can be an object or a function. If a function, it will be passed a FlowFile, a FlowChunk object and a isTest boolean (Default: {})';
//    $parameters['headers'] = 'Extra headers to include in the multipart POST with data. If a function, it will be passed a FlowFile, a FlowChunk object and a isTest boolean (Default: {})';
//    $parameters['withCredentials'] = 'Standard CORS requests do not send or set any cookies by default. In order to include cookies as part of the request, you need to set the withCredentials property to true. (Default: false)';
//    $parameters['method'] = 'Method to use when POSTing chunks to the server (multipart or octet) (Default: multipart)';
//    $parameters['testMethod'] = 'HTTP method to use when chunks are being tested. If set to a function, it will be passed a FlowFile and a FlowChunk arguments. (Default: GET)';
//    $parameters['uploadMethod'] = 'HTTP method to use when chunks are being uploaded. If set to a function, it will be passed a FlowFile and a FlowChunk arguments. (Default: POST)';
//    $parameters['allowDuplicateUploads'] = 'Once a file is uploaded, allow reupload of the same file. By default, if a file is already uploaded, it will be skipped unless the file is removed from the existing Flow object. (Default: false)';
//    $parameters['prioritizeFirstAndLastChunk'] = 'Prioritize first and last chunks of all files. This can be handy if you can determine if a file is valid for your service from only the first or last chunk. For example, photo or video meta data is usually located in the first part of a file, making it easy to test support from only the first chunk. (Default: false)';
//    $parameters['testChunks'] = 'Make a GET request to the server for each chunks to see if it already exists. If implemented on the server-side, this will allow for upload resumes even after a browser crash or even a computer restart. (Default: true)';
//    $parameters['preprocess'] = 'Optional function to process each chunk before testing & sending. To the function it will be passed the chunk as parameter, and should call the preprocessFinished method on the chunk when finished. (Default: null)';
//    $parameters['changeRawDataBeforeSend'] = 'Optional function to change Raw Data just before the XHR Request can be sent for each chunk. To the function, it will be passed the chunk and the data as a Parameter. Return the data which will be then sent to the XHR request without further modification. (Default: null). This is helpful when using FlowJS with Google Cloud Storage. Usage example can be seen #276. (For more, check issue #170).';
//    $parameters['initFileFn'] = 'Optional function to initialize the fileObject. To the function it will be passed a FlowFile and a FlowChunk arguments.';
//    $parameters['readFileFn'] = 'Optional function wrapping reading operation from the original file. To the function it will be passed the FlowFile, the startByte and endByte, the fileType and the FlowChunk.';
//    $parameters['generateUniqueIdentifier'] = 'Override the function that generates unique identifiers for each file. (Default: null)';
//    $parameters['maxChunkRetries'] = 'The maximum number of retries for a chunk before the upload is failed. Valid values are any positive integer and undefined for no limit. (Default: 0)';
//    $parameters['chunkRetryInterval'] = 'The number of milliseconds to wait before retrying a chunk on a non-permanent error. Valid values are any positive integer and undefined for immediate retry. (Default: undefined)';
//    $parameters['progressCallbacksInterval'] = 'The time interval in milliseconds between progress reports. Set it to 0 to handle each progress callback. (Default: 500)';
//    $parameters['speedSmoothingFactor'] = 'Used for calculating average upload speed. Number from 1 to 0. Set to 1 and average upload speed wil be equal to current upload speed. For longer file uploads it is better set this number to 0.02, because time remaining estimation will be more accurate. This parameter must be adjusted together with progressCallbacksInterval parameter. (Default 0.1)';
//    $parameters['successStatuses'] = 'Response is success if response status is in this list (Default: [200,201, 202])';
//    $parameters['permanentErrors'] = 'Response fails if response status is in this list (Default: [404, 415, 500, 501])';

		return \PHPFUI\TextHelper::arrayToJS($parameters);
		}
	}
