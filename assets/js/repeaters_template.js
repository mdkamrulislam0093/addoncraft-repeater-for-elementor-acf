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

            var $panel = $('#elementor-panel-inner');

            var $nav = $(
                '<div id="repefoel-back-nav">' +
                    '<button id="repefoel-back-btn">' +
                        '<i class="eicon-arrow-left" aria-hidden="true"></i>' +
                        '<span>' + REPEFOELTemplateData.backLabel + '</span>' +
                    '</button>' +
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
        }

        function repefoel_open_template_inline( template_id, template_title ) {
            _repefoel_origin_doc_id = elementor.documents.getCurrent().id;

            $e.run('editor/documents/switch', {
                id: parseInt( template_id ),
                mode: 'autosave'
            }).then(function() {
                repefoel_show_back_nav( _repefoel_origin_doc_id, template_title );
            }).catch(function(err) {
                console.error('REPEFOEL: document switch failed', err);
            });
        }

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
                                    repefoel_open_template_inline( sourcetemplate_id, sourcetemplate_title );
                                }
                            });

                            editor.$el.find('button[data-action="add"]').on('click', function(e){
                                e.preventDefault();
                                var $this = $(this);
                                var main_wrap = editor.$el;

                              jQuery.ajax({
                                  url: REPEFOELTemplateData.ajax_url,
                                  method: 'POST',
                                  data: {
                                    'action' : 'repefoel_create_elementor_repeater_template',
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

                                        repefoel_open_template_inline( response.data.template_id, response.data.template_title );
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
