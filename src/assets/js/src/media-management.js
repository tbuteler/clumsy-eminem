(function($, window, document, undefined) {

    var pluginName = 'mediaBox';

    function Plugin(element, options) {

        this.el = element;

        this.$el = $(element);

        this.options = $.extend({}, $.fn[pluginName].defaults, options);

        if (this.$el.hasClass('preview-image')) {
            this.options.preview = 'image';
        } else if (this.$el.hasClass('preview-name')) {
            this.options.preview = 'name';
        }

        this.init();
    }

    Plugin.prototype = {

        init: function() {

            var options = this.options,
                id = this.$el.attr('id'),
                $box = this.$el,
                $modal = $('#'+id+'-modal'),
                $dropzone = $modal.find('.drag-and-drop'),
                previewElement = this._previewElement(),
                dragenterElement;

            $box.closest('.fileupload-group').find('input').fileupload({
                dataType: 'json',
                dropZone: $box.add($dropzone),
                pasteZone: null,
                formData: [
                    {
                        name: 'association',
                        value: this.options.association,
                    },
                    {
                        name: '_token',
                        value: $box.closest('form').find('input[name="_token"]').val()
                    }
                ],
                submit: function(e, data) {
                    $box.removeClass('dragover with-error empty').addClass('uploading').find(previewElement).remove();
                },
                progressall: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $box.find('.progress-bar').css(
                        'width',
                        progress + '%'
                    );
                },
                done: function(e, data) {
                    $box.removeClass('uploading');
                    $box.html(options.allowMultiple ? $box.data('raw') : '');
                    $.each(data.result.files, function (index, file) {
                        if (file.status === 'error') {
                            $box.addClass('with-error');
                            alert(file.message);
                            return true;
                        }
                        if (options.preview === 'image') {
                            $('<img/>')
                                .attr('src', file.preview)
                                .attr('data-src', file.src)
                                .attr('data-media-id', file.mediaId)
                                .appendTo($box);
                        } else if (options.preview === 'name') {
                            if (!$('ol', $box).length) {
                                $('<ol></ol>').appendTo($box);
                            }
                            $('<li></li>')
                                .attr('data-src', file.src)
                                .attr('data-media-id', file.mediaId)
                                .text(file.filename)
                                .appendTo($('ol', $box));
                        }
                        $box.mediaBox('snapshot');
                        $box.closest('form').append(file.input);
                        if (options.allowMultiple) {
                            $modal.find('.current-media').append(file.html);
                            $box.mediaBox('increment');
                        }
                        else {
                            $modal.find('.current-media').html(file.html);
                            $box.mediaBox('setCount', 1);
                        }
                        $box.mediaBox('updateModal');
                    });

                    $box.trigger("uploaded.mediaBox", [data]);
                },
                fail: function(e, data) {
                    if (typeof data.jqXHR.responseJSON !== 'undefined' && typeof data.jqXHR.responseJSON.message !== 'undefined') {
                        alert(data.jqXHR.responseJSON.message);
                    }
                    else {
                        alert(handover.eminem.general_error);
                    }
                    $box.addClass('with-error');
                    $box.mediaBox('checkEmpty');
                    $box.mediaBox('update');

                    $box.trigger("failed.mediaBox", [data]);
                },
                stop: function(e, data) {
                    if (options.allowMultiple) {
                        $box.imagesLoaded( function() {
                            $box.mediaBox('update');
                        });
                    }
                    $box.mediaBox('checkEmpty');
                }
            });

            $box.click(function(e){
                e.preventDefault();
                $modal.modal();
            });

            $box.add($dropzone)
                .on('dragenter', function(e) {
                    dragenterElement = event.target;
                    $(this).addClass('dragover');
                })
                .on('dragleave drop', function(e) {
                    if (dragenterElement === event.target) {
                        $(this).removeClass('dragover');
                    }
                });

            $dropzone.find('button').click(function(e){
                e.preventDefault();
                $box.closest('.fileupload-group').find('input').click();
            });

            this.update();
        },

        destroy: function() {

            this._raw();
            this.$el.removeData();
        },

        update: function() {

            this.$el.find('.fileupload-wrapper').hide();
            this._raw();
            if (this.options.preview === 'image') {
                this._updateGrid();
            }
            this.$el.find('.fileupload-wrapper').fadeIn('fast');
        },

        remove: function(data) {

            var $box = this.$el;

            this._raw();

            // Remove images from media box using src attribute
            if (this.options.preview) {
                this.$el.find(this._previewElement()).each(function(i,el){
                    if ($(el).data('src') === data.src) {
                        $box.trigger("removed.mediaBox", [{
                            media_id: $(el).attr('data-media-id')
                        }]);
                        $(el).remove();
                        return false;
                    }
                });
            }

            // Remove images that would be added on save
            $box.closest('form').find('input[data-media-id="'+data.mediaId+'"]').remove();

            this.decrement();
            this.snapshot();
            this.checkEmpty();
            this.update();
        },

        updateModal: function() {

            var id = this.$el.attr('id'),
                $modal = $('#'+id+'-modal');

            if (!$modal.find('.media-item').length) {
                $modal.find('.upload-a').tab('show');
                $modal.find('.current-a').closest('li').addClass('hidden');
            } else if ($modal.find('.current-a').closest('li').hasClass('hidden')) {
                $modal.find('.current-a').closest('li').removeClass('hidden');
                $modal.find('.current-a').tab('show');
            }
        },

        getCount: function() {
            return parseInt(this.$el.data('count'));
        },

        setCount: function(count) {
            return this.$el.data('count', count);
        },

        increment: function() {
            this.setCount(this.getCount()+1);
        },

        decrement: function() {
            this.setCount(this.getCount()-1);
        },

        isEmpty: function() {
            return this.getCount() === 0;
        },

        checkEmpty: function() {
            if (this.isEmpty()) {
                this.$el.addClass('empty');
            }
            else {
                this.$el.removeClass('empty');
            }
        },

        snapshot: function() {
            if (this.options.allowMultiple) {
                this.$el.data('raw', this.$el.html());
            }
        },

        _previewElement: function() {
            return this.options.preview === 'image' ? 'img' : 'li';
        },

        _raw: function() {
            if (this.options.allowMultiple) {
                this.$el.html(this.$el.data('raw'));
            }
        },

        _updateGrid: function() {

            if (!this.options.allowMultiple) {
                return false;
            }

            // Reset plugin data, so it inits properly
            this.$el.removeData('plugin_photosetGrid').removeAttr('data-width');
            this.$el.data('raw', this.$el.html());
            var count = this.$el.find('img').length,
                columns = count,
                layout = '';
            if (count % 2 === 0 && count % 3 === 0) {
                columns = 3;
            } else {
                columns = count % 2 === 0 ? 2 : 3;
            }
            var rows = Math.ceil(count/columns);
            for (i = 0; i < rows; i++) {
                layout += count < columns ? count : columns;
                count -= columns;
            }
            this.$el.photosetGrid({
                gutter: '2px',
                layout: layout
            });
        }
    };

    $.fn[pluginName] = function(options) {
        var args = arguments;

        if (options === undefined || typeof options === 'object') {
            return this.each(function() {
                if (!$.data(this, 'plugin_' + pluginName)) {
                    $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
                }
            });
        } else if (typeof options === 'string' && options[0] !== '_' && options !== 'init') {
            if (Array.prototype.slice.call(args, 1).length === 0 && $.inArray(options, $.fn[pluginName].getters) !== -1) {
                var instance = $.data(this[0], 'plugin_' + pluginName);
                return instance[options].apply(instance, Array.prototype.slice.call(args, 1));
            } else {
                return this.each(function() {
                    var instance = $.data(this, 'plugin_' + pluginName);
                    if (instance instanceof Plugin && typeof instance[options] === 'function') {
                        instance[options].apply(instance, Array.prototype.slice.call(args, 1));
                    }
                });
            }
        }
    };

    $.fn[pluginName].defaults = {
        allowMultiple: false,
        preview: false
    };

})(jQuery, window, document);

$(function() {

    if (typeof handover !== 'undefined' && typeof handover.eminem !== 'undefined') {

        for (var slot in handover.eminem.boxes) {
            var media = handover.eminem.boxes[slot];
            $('#'+media.id).mediaBox({
                allowMultiple: media.allowMultiple,
                association: media.association
            });
        }

        $(document).on('drop dragover', function(e) {
            e.preventDefault();
        });

        $(document).on('click', '.media-unbind', function(){
            var $item = $(this).closest('.media-item'),
                $current = $item.closest('.current-media'),
                slug = $current.closest('.tab-pane').attr('id').replace('-current', ''),
                $box = $('#'+slug),
                $img = $item.find('img'),
                bind_id = $(this).data('id');

            $item.fadeOut('fast', function(){
                $item.remove();
                $box.mediaBox('remove', $img.data());
                $box.mediaBox('updateModal');
                if (bind_id !== '') {
                    var unbind = '<input type="hidden" name="media_unbind[]" value="'+bind_id+'" />';
                    $box.closest('form').append(unbind);
                }
            });
        });

        $(document).on('submit', 'form.meta', function(event){
            event.preventDefault();

            var $el = $(this);
            var $btn = $(this).find('.media-save-meta');
            $el.prop('disabled', true).siblings('button').prop('disabled', true);

            var url = $el.attr('action');
            var data = $el.serialize();

            var $i_active = $el.find('.meta-save-active');
            var $i_loading = $el.find('.meta-save-loading');
            var $i_success = $el.find('.meta-save-success');

            $i_active.hide();
            $i_loading.show();

            $.post(url,data,function(data) {
                    if (data.status == 'error') {
                        alert(data.message);
                    }
                    else {
                        $i_loading.fadeOut().promise().done(function(){
                            $i_success.fadeIn('fast');
                            $btn.prop('disabled',false);
                            $btn.siblings('button').prop('disabled',false);
                        });
                        setTimeout(function(){
                            $i_success.fadeOut('fast').promise().done(function(){
                                $i_active.fadeIn('fast');
                            });
                        }, 1500);
                    }
                }
            );
        });

        $('.fileupload-group [data-toggle="popover"]').popover();
    }
});
