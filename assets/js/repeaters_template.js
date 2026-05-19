/**
 * Custom Elementor Template JavaScript
 * Save this as: assets/custom-template.js
 */

(function($) {
    'use strict';

    // Wait for Elementor to be fully loaded
    $(window).on('elementor:init', function() {

        // Add custom template type to create new template dialog
        elementor.hooks.addFilter('elementor/template-library/create_new_dialog_types', function(types) {
            types.custom_template = {
                title: REPEFOELTemplateData.customTemplate,
                icon: 'eicon-document-file'
            };
            return types;
        });

        // Handle template library source local
        elementor.hooks.addFilter('elementor/template-library/sources/local/template_types', function(templateTypes) {
            templateTypes.custom_template = {
                title: REPEFOELTemplateData.customTemplate,
                data: {
                    template_type: 'custom_template'
                }
            };
            return templateTypes;
        });

        // Add template type to the library categories
        elementor.hooks.addFilter('elementor/template-library/template_types', function(templateTypes) {
            templateTypes.custom_template = {
                title: REPEFOELTemplateData.customTemplate,
                data: {
                    template_type: 'custom_template'
                }
            };
            return templateTypes;
        });

        // Modify the template library modal
        elementor.hooks.addAction('elementor/template-library/modal_shown', function() {
            // Add custom template tab if it doesn't exist
            var $templatesModal = $('#elementor-template-library-modal');
            var $tabsWrapper = $templatesModal.find('.elementor-template-library-menu');

            if (!$tabsWrapper.find('[data-template-type="custom_template"]').length) {
                var customTab = `
                    <div class="elementor-template-library-menu-item" data-template-type="custom_template">
                        <i class="eicon-document-file"></i>
                        <span class="elementor-template-library-menu-item-title">${REPEFOELTemplateData.plural}</span>
                    </div>
                `;
                $tabsWrapper.append(customTab);
            }
        });

        // Handle custom template creation
        elementor.hooks.addAction('elementor/template-library/save_template', function(templateData) {
            if (templateData.template_type === 'custom_template' && elementor.notifications) {
                elementor.notifications.showToast({
                    message: REPEFOELTemplateData.successMessage
                });
            }
        });

        // Customize template item rendering
        elementor.hooks.addFilter('elementor/template-library/template_item_view', function(ItemView) {
            return ItemView.extend({
                className: function() {
                    var classes = ItemView.prototype.className.call(this);
                    if (this.model.get('template_type') === 'custom_template') {
                        classes += ' elementor-template-library-template-custom';
                    }
                    return classes;
                },

                onRender: function() {
                    ItemView.prototype.onRender.call(this);

                    if (this.model.get('template_type') === 'custom_template') {
                        this.$el.find('.elementor-template-library-template-body')
                            .append('<div class="elementor-template-library-template-badge elementor-template-library-template-badge-custom">Custom</div>');
                    }
                }
            });
        });

        // Handle template insertion
        elementor.hooks.addAction('elementor/template-library/before_insert_template', function(templateModel) {
            if (templateModel.get('template_type') === 'custom_template') {
                console.log('Inserting custom template:', templateModel.get('title'));
            }
        });

        // Listen for the custom control event
        elementor.channels.editor.on('REPEFOEL:apply_preview', async function() {

            try {
                await elementor.saver.saveEditor({
                    status: elementor.settings.page.model.get('post_status')
                });

                elementor.reloadPreview();

            } catch (err) {
                console.error('Error while reloading preview:', err);
            }

        });


        // ── Inline template editing (same-tab, Elementor document switch) ─

        var _repefoel_origin_doc_id = null;

        function repefoel_show_back_nav( origin_doc_id, template_title ) {
            $('#repefoel-back-nav').remove();

            var $panel = $('#elementor-preview');

            var $nav = $(
                '<div id="repefoel-back-nav">' +
                    '<div class="repefoel-back-wrap"><button id="repefoel-back-btn">' +
                        '<i class="eicon-arrow-left" aria-hidden="true"></i>' +
                        '<span>' + REPEFOELTemplateData.backLabel + '</span>' +
                    '</button>' +
                    '<button id="repefoel-publish-btn">' +
                        '<span>' + ( REPEFOELTemplateData.publishLabel || 'Publish Changes' ) + '</span>' +
                    '</button></div>' +
                    '<span id="repefoel-editing-label">' +
                        '<i class="eicon-loop" aria-hidden="true"></i>' +
                        '<span>' + ( template_title || REPEFOELTemplateData.editingTemplate ) + '</span>' +
                    '</span>' +
                '</div>'
            );

            $panel.prepend( $nav );

            $('#repefoel-back-btn').on('click', async function() {
                var doc_id = origin_doc_id;
                $('#repefoel-back-nav').remove();

                try {
                    await $e.run('editor/documents/switch', {
                        id: parseInt( doc_id ),
                        mode: 'autosave'
                    });
                } catch (err) {
                    console.error('REPEFOEL: document switch back failed', err);
                }

                // Reload the parent page preview so template edits show up.
                setTimeout(function() {
                    try { elementor.reloadPreview(); } catch(e) {}
                }, 300);
            });

            $('#repefoel-publish-btn').on('click', async function() {
                var $btn = $(this);
                $btn.prop('disabled', true).addClass('repefoel-publishing');

                try {
                    await $e.run('document/save/publish');
                    $btn.addClass('repefoel-publish-done');
                    setTimeout(function() {
                        $btn.prop('disabled', false).removeClass('repefoel-publishing repefoel-publish-done');
                    }, 1500);
                } catch (err) {
                    console.error('REPEFOEL: publish failed', err);
                    $btn.prop('disabled', false).removeClass('repefoel-publishing');
                }
            });
        }

        function repefoel_select_first_container() {
            var attempts = 0;
            var interval = setInterval(function() {
                if ( ++attempts > 25 ) {
                    clearInterval(interval);
                    return;
                }
                try {
                    var previewView = elementor.getPreviewView();
                    if ( !previewView || !previewView.children || !previewView.children.length ) {
                        return;
                    }
                    var firstView = previewView.children.first();
                    if ( !firstView || !firstView.container ) {
                        return;
                    }
                    clearInterval(interval);
                    $e.run('document/elements/select', { container: firstView.container });
                } catch(e) {
                    // keep retrying
                }
            }, 200);
        }

        function repefoel_open_template_inline( template_id, template_title, isSlider ) {
            _repefoel_origin_doc_id = elementor.documents.getCurrent().id;

            $e.run('editor/documents/switch', {
                id: parseInt( template_id ),
                mode: 'autosave'
            }).then(function() {
                repefoel_show_back_nav( _repefoel_origin_doc_id, template_title );
                if ( isSlider ) {
                    repefoel_inject_slider_grid_css();
                }
                repefoel_select_first_container();
            }).catch(function(err) {
                console.error('REPEFOEL: document switch failed', err);
            });
        }

        function repefoel_inject_slider_grid_css() {
            setTimeout(function() {
                try {
                    var $head = elementor.$preview.contents().find('head');
                    if ( $head.length && !$head.find('#repefoel-slider-grid-css').length ) {
                        $head.append(
                            '<style id="repefoel-slider-grid-css">' +
                            '.repefoel_acf_repeater_rs_sliders { overflow: visible !important; }' +
                            '.repefoel_acf_repeater_rs_sliders .swiper-wrapper {' +
                                'transform: none !important;' +
                                'flex-wrap: wrap !important;' +
                                'height: auto !important;' +
                            '}' +
                            '.repefoel_acf_repeater_rs_sliders .swiper-slide {' +
                                'width: calc(33.333% - 10px) !important;' +
                                'margin-right: 10px !important;' +
                                'margin-bottom: 10px !important;' +
                            '}' +
                            '.acf_repeater_rs_swiper_product_sliders-nav { display: none !important; }' +
                            '</style>'
                        );
                    }
                } catch(e) {
                    console.error('REPEFOEL: failed to inject grid preview CSS', e);
                }
            }, 600);
        }

        function repefoel_apply_preview_settings_then_open( template_id, template_title, post_id, repeater_field, isSlider ) {
            jQuery.ajax({
                url: REPEFOELTemplateData.ajax_url,
                method: 'POST',
                data: {
                    action:         'repefoel_update_template_preview_settings',
                    template_id:    template_id,
                    post_id:        post_id,
                    repeater_field: repeater_field,
                },
                complete: function() {
                    repefoel_open_template_inline( template_id, template_title, isSlider );
                }
            });
        }

        // ── Auto-restore back nav on direct URL load (page reload with active-document param) ─
        (function() {
            var urlParams    = new URLSearchParams(window.location.search);
            var urlPostId    = parseInt(urlParams.get('post'), 10);
            var urlActiveDoc = parseInt(urlParams.get('active-document'), 10);

            if (!urlActiveDoc || !urlPostId || urlActiveDoc === urlPostId) {
                return;
            }

            var attempts = 0;
            var check = setInterval(function() {
                if (++attempts > 150) {
                    clearInterval(check);
                    return;
                }

                if ($('#repefoel-back-nav').length) {
                    clearInterval(check);
                    return;
                }

                if (!$('#elementor-preview').length) {
                    return;
                }

                clearInterval(check);
                var doc   = (elementor.documents && elementor.documents.get(urlActiveDoc)) || null;
                var title = doc ? doc.get('title') : '';
                repefoel_show_back_nav(urlPostId, title);
            }, 200);
        })();

        // ─────────────────────────────────────────────────────────────────

        elementor.channels.editor.on('section:activated', (secionName, editor) => {
            // const sectionName = model?.getOption('name');
            if ( secionName == 'content_section' ) {
                var model = editor.getOption('editedElementView').getEditModel();
                var currentElementType = model.get('elType');

                if ('widget' === currentElementType) {
                    currentElementType = model.get('widgetType');

                    if (
                            [
                                'REPEFOEL_widget_repeater',
                                'REPEFOEL_widget_repeater_carousel',
                                'REPEFOEL_widget_post_repeater'
                            ].includes(currentElementType)
                        ) {
                        // Repeater and Slider have REPEFOEL_repeater_field; Post Repeater uses post type.
                        var hasRepeaterField = [
                            'REPEFOEL_widget_repeater',
                            'REPEFOEL_widget_repeater_carousel'
                        ].includes(currentElementType);

                        // Slider needs grid-mode CSS injected into preview while editing.
                        var isSlider = ( currentElementType === 'REPEFOEL_widget_repeater_carousel' );

                        setTimeout(function () {

                            var sourceControl = editor.$el.find('[data-setting="repefoel_template_select"]');
                            var post_id = elementor.config.document.id;

                            if ( sourceControl.length ) {
                                var current_template_value = sourceControl.val();

                                if ( current_template_value != '' ) {
                                    editor.$el.find('button[data-action="add"]').hide();
                                    editor.$el.find('button[data-action="edit"]').show();
                                } else {
                                    editor.$el.find('button[data-action="add"]').show();
                                    editor.$el.find('button[data-action="edit"]').hide();
                                }

                                sourceControl.on('change', function(e){
                                    e.preventDefault();
                                    let current_val = $(this).val();

                                    if ( current_val != '' ) {
                                        editor.$el.find('button[data-action="add"]').hide();
                                        editor.$el.find('button[data-action="edit"]').show();
                                    } else {
                                        editor.$el.find('button[data-action="add"]').show();
                                        editor.$el.find('button[data-action="edit"]').hide();
                                    }

                                });
                            }

                            editor.$el.find('button[data-action="edit"]').on('click', function(){

                                var repeaterFieldVal = '';

                                if ( hasRepeaterField ) {
                                    var $repeaterField = editor.$el.find('[data-setting="REPEFOEL_repeater_field"]');
                                    repeaterFieldVal = $repeaterField.val();
                                    if ( !$repeaterField.length || !repeaterFieldVal || repeaterFieldVal === '' ) {
                                        editor.$el.find('#repefoel-field-notice').remove();
                                        var $notice = $('<div id="repefoel-field-notice" style="color:#d63638;font-size:11px;margin:6px 0 0;padding:6px 8px;background:#fce9e9;border:1px solid #f5c5c5;border-radius:3px;">Please select a <strong>Repeater Field</strong> before editing a template.</div>');
                                        $repeaterField.closest('.elementor-control').after($notice);
                                        setTimeout(function() { $notice.fadeOut(300, function(){ $(this).remove(); }); }, 3500);
                                        return;
                                    }
                                }

                                var sourcetemplate_id = '';
                                var sourcetemplate_title = '';

                                if ( $(this).attr('data-template_id') ) {
                                    sourcetemplate_id = $(this).attr('data-template_id');
                                } else {
                                    var $sourcetemplate = editor.$el.find('[data-setting="repefoel_template_select"]');

                                    if ( $sourcetemplate.length ) {
                                        sourcetemplate_id    = $sourcetemplate.val();
                                        sourcetemplate_title = $sourcetemplate.find('option:selected').text();
                                    }
                                }

                                if ( sourcetemplate_id != '' ) {
                                    repefoel_apply_preview_settings_then_open(
                                        sourcetemplate_id,
                                        sourcetemplate_title,
                                        elementor.config.document.id,
                                        repeaterFieldVal,
                                        isSlider
                                    );
                                }
                            });

                            editor.$el.find('button[data-action="add"]').on('click', function(e){
                                e.preventDefault();

                                var repeaterFieldVal = '';

                                if ( hasRepeaterField ) {
                                    var $repeaterField = editor.$el.find('[data-setting="REPEFOEL_repeater_field"]');
                                    repeaterFieldVal = $repeaterField.val();
                                    if ( !$repeaterField.length || !repeaterFieldVal || repeaterFieldVal === '' ) {
                                        editor.$el.find('#repefoel-field-notice').remove();
                                        var $notice = $('<div id="repefoel-field-notice" style="color:#d63638;font-size:11px;margin:6px 0 0;padding:6px 8px;background:#fce9e9;border:1px solid #f5c5c5;border-radius:3px;">Please select a <strong>Repeater Field</strong> before adding a template.</div>');
                                        $repeaterField.closest('.elementor-control').after($notice);
                                        setTimeout(function() { $notice.fadeOut(300, function(){ $(this).remove(); }); }, 3500);
                                        return;
                                    }
                                }

                                var $this = $(this);
                                var main_wrap = editor.$el;
                                var current_post_id = elementor.config.document.id;

                              jQuery.ajax({
                                  url: REPEFOELTemplateData.ajax_url,
                                  method: 'POST',
                                  data: {
                                    'action'         : 'repefoel_create_elementor_repeater_template',
                                    'post_id'        : current_post_id,
                                    'repeater_field' : repeaterFieldVal,
                                  },
                                  success: function(response) {

                                    if ( response['success'] ) {
                                        $this.hide();
                                        main_wrap.find('button[data-action="edit"]').attr('data-template_id', response.data.template_id);
                                        main_wrap.find('[data-setting="repefoel_template_select"]').prepend($('<option>', {
                                            value: response.data.template_id,
                                            text: response.data.template_title
                                        }));

                                        main_wrap.find('[data-setting="repefoel_template_select"]').val(response.data.template_id).change();
                                        main_wrap.find('button[data-action="edit"]').show();

                                        editor.$el.find('#elementor-controls').attr('data-template_id', response.data.template_id).attr('data-template_title', response.data.template_title);

                                        repefoel_open_template_inline( response.data.template_id, response.data.template_title, isSlider );
                                    }

                                  }
                              });
                            });

                            if ( post_id != '' ) {
                                const settings = editor.getOption('model').attributes.settings.attributes ?? '';
                                const temporary_template_id = editor.$el.find('#elementor-controls').attr('data-template_id') ?? '';

                                if ( settings['repefoel_template_select'] != '' && temporary_template_id == settings['repefoel_template_select'] ) {
                                    const temporary_template_title = editor.$el.find('#elementor-controls').attr('data-template_title') ?? '';
                                    editor.$el.find('[data-setting="repefoel_template_select"]').prepend($('<option>', {
                                        value: temporary_template_id,
                                        text: temporary_template_title
                                    })).val(temporary_template_id);
                                    editor.$el.find('button[data-action="edit"]').attr('data-template_id', temporary_template_id);
                                }

                            }


                        }, 10);
                    }
                }
            }
        });


    });


    // Additional DOM ready handlers
    $(document).ready(function() {
        // Handle admin template type selection
        $(document).on('change', 'select[name="elementor_library_type"]', function() {
            $('.custom-template-settings').toggle($(this).val() === 'custom_template');
        });

        // Initialize on page load
        if ($('select[name="elementor_library_type"]').val() === 'custom_template') {
            $('.custom-template-settings').show();
        }

        if (typeof elementor !== 'undefined' && elementor.config.document?.type === 'custom_template') {
            $('body').addClass('elementor-editor-custom-template');
        }
    });



})(jQuery);
