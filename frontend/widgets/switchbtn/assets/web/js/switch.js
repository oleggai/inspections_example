
var SWITCH = {

    processUser: function() {

        $('[data-checkbox-toggle]').click(function() {

            var labelElem = $('[for=' + $(this).attr('id') + ']');

            var confirmMessage = $(labelElem).attr('data-switch-confirm-message');
            var url = $(labelElem).attr('data-url');

            if(confirmMessage) {
                bootbox.confirm({
                        message: confirmMessage,
                        className: 'small-modal',
                        buttons: {
                            confirm: {
                                label: 'Так',
                                className: 'btn-success'
                            },
                            cancel: {
                                label: 'Ні',
                                className: 'btn-danger'
                            }
                        },
                        callback: function(result) {

                            if(result) {

                                $.ajax({
                                    dataType: 'json',
                                    url: url,
                                    type: 'post',

                                    error: function(xhr, status, error) {

                                        alert('Вибачте відбулась помилка');

                                    }

                                }).done(function(data) {

                                    if('redirect' in data) {
                                        window.location.href = data.redirect;
                                    } else {
                                        window.location.reload();
                                    }
                                });

                            }
                        }
                    }
                );
            }

            return false;
        });
    },

    processTooltip: function() {
        
        $('[data-checkbox-toggle]').change(function() {

            var labelElem = $('[for=' + $(this).attr('id') + ']');
            
            if($(this).is(':checked')) {
                
                $(labelElem).attr('title', $(labelElem).attr('data-tooltip-active'))
                    .tooltip('fixTitle')
                    .tooltip('show');
            } else {
                $(labelElem).attr('title', $(labelElem).attr('data-tooltip-passive'))
                    .tooltip('fixTitle')
                    .tooltip('show');
            }
        });
    }
};