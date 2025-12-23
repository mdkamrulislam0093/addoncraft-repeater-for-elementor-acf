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


        elementor.channels.editor.on('section:activated', (secionName, editor) => {
            // const sectionName = model?.getOption('name');
            if ( secionName == 'content_section' ) {
                var model = editor.getOption('editedElementView').getEditModel();
                var currentElementType = model.get('elType');

                if ('widget' === currentElementType) {
                    currentElementType = model.get('widgetType');

                    if ( 'REPEFOEL_widget_repeater' == currentElementType ) {
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

                                if ( $(this).attr('data-template_id') ) {
                                    sourcetemplate_id = $(this).attr('data-template_id');
                                } else {
                                    var sourcetemplate = editor.$el.find('[data-setting="repefoel_template_select"]');

                                    if ( sourcetemplate.length ) {
                                        sourcetemplate_id = sourcetemplate.val();
                                    }
                                }

                                if ( sourcetemplate_id != '' ) {
                                    window.open("/wp-admin/post.php?post="+ sourcetemplate_id +"&action=elementor");
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

                                        window.open(response.data.edit_url, '_blank');
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
