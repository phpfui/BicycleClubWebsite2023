<?php

namespace App\UI;

class ChunkedUploader
	{
	private array $options = [];

	public function __construct(private readonly \PHPFUI\Interfaces\Page $page)
		{
		$this->page->addTailScript('/flow.min.js');
		$this->page->addStyleSheet('/style.css');
		}

	public function getError() : \PHPFUI\HTML5Element
		{
		$div = new \PHPFUI\HTML5Element('div');
		$div->addClass('flow-error');
		$div->addClass('hide');
		$div->add('Your browser, unfortunately, is not supported. The library requires support for <a href="http://www.w3.org/TR/FileAPI/">the HTML5 File API</a> along with <a href="http://www.w3.org/TR/FileAPI/#normalization-of-params">file slicing</a>.');

		return $div;
		}

	public function getUploadArea(\PHPFUI\Container $text, \PHPFUI\Button $selectButton) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$dropArea = new \PHPFUI\HTML5Element('div');
		$dropArea->addClass('flow-drop');
		$dropArea->addAttribute('ondragenter', 'jQuery(this).addClass("flow-dragover")');
		$dropArea->addAttribute('ondragend', 'jQuery(this).removeClass("flow-dragover")');
		$dropArea->addAttribute('ondrop', 'jQuery(this).removeClass("flow-dragover")');
		$selectButton->addClass('flow-browse');
		$dropArea->add($text);
		$container->add($dropArea);

		$container->add(
			<<<HTML
<div class="flow-progress">
<table>
<tr>
<td width="100%"><div class="progress-container"><div class="progress-bar"></div></div></td>
<td class="progress-text" nowrap="nowrap"></td>
<td class="progress-pause" nowrap="nowrap">
<a href="#" onclick="r.upload(); return(false);" class="progress-resume-link"><img src="/resume.png" title="Resume upload" /></a>
<a href="#" onclick="r.pause(); return(false);" class="progress-pause-link"><img src="/pause.png" title="Pause upload" /></a>
<a href="#" onclick="r.cancel(); return(false);" class="progress-cancel-link"><img src="/cancel.png" title="Cancel upload" /></a>
</td>
</tr>
<tr>
<td width="100%">You must not leave this page or switch to another tab till the video is 100% uploaded.</td>
</tr>
</table>
</div>
HTML
		);

		$options = \PHPFUI\TextHelper::arrayToJS($this->options);

		$ul = new \PHPFUI\UnorderedList();
		$ul->addClass('flow-list');
		$container->add($ul);

		$js = '(function () {var r = new Flow(';
		$js .= \PHPFUI\TextHelper::arrayToJS($this->options);
		$js .= ');';
		// Flow.js isn't supported, fall back on a different method
		$js .= 'if (!r.support) {$(".flow-error").show();return ;}';
		// Show a place for dropping/selecting files
		$js .= '$(".flow-drop").show();
r.assignDrop($(".flow-drop")[0]);
r.assignBrowse($(".flow-browse")[0]);
r.assignBrowse($(".flow-browse-folder")[0], true);
r.assignBrowse($(".flow-browse-image")[0], false, false, {accept: "image/*"});';

	// Handle file add event
	$js .= 'r.on("fileAdded", function(file){';
	// Show progress bar
	$js .= '$(".flow-progress, .flow-list").show();';
	// Add the file to the list
	$js .= '$(".flow-list").append(';
	$js .= "'<li class=\"flow-file flow-file-'+file.uniqueIdentifier+'\">' + " .
			"'Uploading <span class=\"flow-file-name\"></span> <span class=\"flow-file-size\"></span> <span class=\"flow-file-progress\"></span> ' + " .
			"'<span class=\"flow-file-pause\"> <img src=\"/pause.png\" title=\"Pause upload\" /></span>' + " .
			"'<span class=\"flow-file-resume\"> <img src=\"/resume.png\" title=\"Resume upload\" /></span>' + " .
			"'<span class=\"flow-file-cancel\"> <img src=\"/cancel.png\" title=\"Cancel upload\" /></span></li>');";
	$js .= 'var $self = $(".flow-file-"+file.uniqueIdentifier);
		$self.find(".flow-file-name").text(file.name);
		$self.find(".flow-file-size").text(readablizeBytes(file.size));
		$self.find(".flow-file-pause").on("click", function () {
			file.pause();
			$self.find(".flow-file-pause").hide();
			$self.find(".flow-file-resume").show();
		});
		$self.find(".flow-file-resume").on("click", function () {
			file.resume();
			$self.find(".flow-file-pause").show();
			$self.find(".flow-file-resume").hide();
		});
		$self.find(".flow-file-cancel").on("click", function () {
			file.cancel();
			$self.remove();
		});
	});
	r.on("filesSubmitted", function(file) {
		r.upload();
	});
	r.on("complete", function(){
		// Hide pause/resume when the upload has completed
		$(".flow-progress .progress-resume-link, .flow-progress .progress-pause-link").hide();
	});
	r.on("fileSuccess", function(file,message){
		var $self = $(".flow-file-"+file.uniqueIdentifier);
		// Reflect that the file upload has completed
		$self.find(".flow-file-progress").text("(completed)");
		$self.find(".flow-file-pause, .flow-file-resume").remove();
	});
	r.on("fileError", function(file, message){
		// Reflect that the file upload has resulted in error
		$(".flow-file-"+file.uniqueIdentifier+" .flow-file-progress").html("(file could not be uploaded: "+message+")");
	});
	r.on("fileProgress", function(file){
		// Handle progress for both the file and the overall upload
		$(".flow-file-"+file.uniqueIdentifier+" .flow-file-progress")
			.html((Math.floor(file.progress()*100)) + "%");
		$(".progress-bar").css({width:Math.floor(r.progress()*100) + "%"});
	});
	r.on("uploadStart", function(){
		// Show pause, hide resume
		$(".flow-progress .progress-resume-link").hide();
		$(".flow-progress .progress-pause-link").show();
	});
	r.on("catchAll", function() {
		console.log.apply(console, arguments);
	});
	window.r = {
		pause: function () {
			r.pause();
			// Show resume, hide pause
			$(".flow-file-resume").show();
			$(".flow-file-pause").hide();
			$(".flow-progress .progress-resume-link").show();
			$(".flow-progress .progress-pause-link").hide();
		},
		cancel: function() {
			r.cancel();
			$(".flow-file").remove();
		},
		upload: function() {
			$(".flow-file-pause").show();
			$(".flow-file-resume").hide();
			r.resume();
		},
		flow: r
	};
})();

function readablizeBytes(bytes) {
	var s = ["by", "kB", "MB", "GB", "TB", "PB"];
	var e = Math.floor(Math.log(bytes) / Math.log(1024));
	return (bytes / Math.pow(1024, e)).toFixed(2) + " " + s[e];
}';
		$this->page->addJavaScript($js);

		return $container;
		}

	public function setOption(string $option, $value = null) : self
		{
		if (null === $value)
			{
			unset($this->options[$option]);
			}
		else
			{
			$this->options[$option] = $value;
			}

		return $this;
		}
	}
