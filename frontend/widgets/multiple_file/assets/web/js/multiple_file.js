
var MULTIPLE_FILE = {

    _myDropzone: null,
    _dropzoneSelector: "#upload-multiple",

    _DELETE_URL: null,
    _UPLOAD_URL: null,

    _hideTextBlock: function() {
        $('[data-text-block]').hide();
    },

    _showTextBlock: function() {
        $('[data-text-block]').show();
    },

    _processClearTemp: function(url) {

        $(window).on('unload', function() {
            var fd = new FormData();
            /*fd.append('temp_key', tempKey);*/

            navigator.sendBeacon(url, fd);
        });
    },

    _deleteTemp: function(fileName) {

        var $this = this;

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: $this._DELETE_URL,
            data: {
                fileName: fileName
            },
            success: function (response) {

            }
        })
    },

    _initDropzone: function(dropzoneSettings) {

        var $this = this;

        Dropzone.autoDiscover = false;

        Dropzone.prototype.defaultOptions.dictFileTooBig = "Файл занадто великий ({{filesize}}MB). Максимальний розмір файлу: {{maxFilesize}}MB.";
        Dropzone.prototype.defaultOptions.dictInvalidFileType = "Ви не можете завантажувати файли з таким розширенням";
/*        Dropzone.prototype.defaultOptions.dictMaxFilesExceeded = "Ви можете завантажити лише один файл.";*/
        Dropzone.prototype.defaultOptions.dictRemoveFile = "Видалити файл";
        Dropzone.prototype.defaultOptions.dictCancelUpload = "Скасувати завантаження";

        /*Dropzone.prototype.defaultOptions.dictDefaultMessage = "Drop files here to upload";
Dropzone.prototype.defaultOptions.dictFallbackMessage = "Your browser does not support drag'n'drop file uploads.";
Dropzone.prototype.defaultOptions.dictFallbackText = "Please use the fallback form below to upload your files like in the olden days.";
Dropzone.prototype.defaultOptions.dictFileTooBig = "File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.";
Dropzone.prototype.defaultOptions.dictInvalidFileType = "You can't upload files of this type.";
Dropzone.prototype.defaultOptions.dictResponseError = "Server responded with {{statusCode}} code.";
Dropzone.prototype.defaultOptions.dictCancelUpload = "Cancel upload";
Dropzone.prototype.defaultOptions.dictCancelUploadConfirmation = "Are you sure you want to cancel this upload?";
Dropzone.prototype.defaultOptions.dictRemoveFile = "Remove file";
Dropzone.prototype.defaultOptions.dictMaxFilesExceeded = "You can not upload any more files.";*/


        $this._myDropzone = new Dropzone($this._dropzoneSelector, $.extend(dropzoneSettings, {
            url: $this._UPLOAD_URL,
            addRemoveLinks: true,
            clickable: [$this._dropzoneSelector, '#add-multiplefile-btn'],
            init: function () {

                $($this._dropzoneSelector).addClass('dropzone');

                this.on("success", function (file, response) {
                    // Handle the responseText here. For example, add the text to the preview element:
                    file.uploadedFileName = response.uploadedFileName;
                });

                this.on('error', function (file, error) {
                    $this._hideTextBlock();
                });

                this.on("processing", function (file) {
                    $this._hideTextBlock();
                });

                this.on('removedfile', function (file) {

                    if(!$($this._dropzoneSelector).find('.dz-preview').length) {
                        $this._showTextBlock();
                    }

                    if (!file.id) {
                        $this._deleteTemp(file.uploadedFileName);
                    } else {
                        var deleteElem = $('[data-deleted-multiple-files-ids]');
                        var deletedIds = $(deleteElem).val();
                        $(deleteElem).val(deletedIds + ',' + file.id);
                    }
                });
            }
        }));
    },

    _createMockFiles: function(mockFiles) {

        var $this = this;

        if(mockFiles.length) {
            $this._hideTextBlock();
        }

        $.each(mockFiles, function (index, mockFile) {

            // Call the default addedfile event handler
            $this._myDropzone.emit("addedfile", mockFile);
            // Or if the file on your server is not yet in the right
            // size, you can let Dropzone download and resize it
            // callback and crossOrigin are optional.
            $this._myDropzone.createThumbnailFromUrl(mockFile, mockFile.url);
            // Make sure that there is no progress bar, etc...
            $this._myDropzone.emit("complete", mockFile);
        });
    },

    process: function(uploadUrl, deleteUrl, clearTempUrl, mockFiles, dropzoneSettings) {

        var $this = this;

        $this._UPLOAD_URL = uploadUrl;
        $this._DELETE_URL = deleteUrl;

        $this._initDropzone(dropzoneSettings);
        $this._createMockFiles(mockFiles);
        $this._processClearTemp(clearTempUrl)
    }
};