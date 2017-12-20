(function($) {

    var Watchlist = {
        onReady: function() {
            this.registerAdd();
            this.registerMultipleAdd();
            this.registerDelete();
            this.registerDeleteAll();
            this.registerWatchlistModal();
            this.registerWatchlistSelect();
            this.registerDownloadLink();
            this.registerMultipleSelectAdd();
        },
        registerMultipleAdd: function() {
            $(document).on('click', '.watchlist-multiple-add', function() {
                var btn = $(this);
                var input = $('#watchlist-input-' + btn.data('id'));
                var durability = $('#watchlist-select-durability-' + btn.data('id')).find(':selected').val();
                if (durability === 'undefined') {
                    durability = 0;
                }
                if (!input.val()) {
                    input.addClass('watchlist-empty-input');
                    return;
                }
                $.ajax({
                    url: $('#watchlist-multiple-add-' + btn.data('id')).data('watchlistAddAction') + '&watchlist=' + input.val() + '&durability=' + durability,
                    success: function(data, textStatus, jqXHR) {
                        Watchlist.showNotification(data.result.html.notification);
                        $('#watchlistModal-' + data.result.html.id).modal('hide');
                    },
                });
            });
        },
        registerMultipleSelectAdd: function() {
            $(document).on('click', '.watchlist-multiple-select-add', function() {
                var btn = $(this);
                var select = $('#watchlist-select-input-' + btn.data('id'));
                if (select.find(':selected').val() <= 0) {
                    select.children('div').addClass('watchlist-empty-input');
                    return;
                }
                $.ajax({
                    url: $('#watchlist-multiple-select-add-' + btn.data('id')).data('watchlistAddAction') + '&watchlist=' + select.find(':selected').val(),
                    success: function(data, textStatus, jqXHR) {
                        Watchlist.showNotification(data.result.html.notification);
                        $('#watchlistModal-' + data.result.html.id).modal('hide');
                    },
                });
            });
        },
        showNotification: function(notification) {
            $('.watchlist-notification').replaceWith(notification);
            $('#watchlist-notify').fadeIn(600);
            setTimeout(function() {
                $('#watchlist-notify').fadeOut();
            }, 3000);
        },
        registerAdd: function() {
            $(document).on('click', '.watchlist-add', function() {
                var btn = $(this);
                $.ajax({
                    url: $('#watchlist-add-' + btn.data('id')).data('watchlistAddAction'),
                    success: function(data, textStatus, jqXHR) {
                        $('#watchlist-add-' + data.result.html.id).removeClass('watchlist-add');
                        $('#watchlist-add-' + data.result.html.id).addClass('watchlist-delete-item watchlist-added');
                        Watchlist.showNotification(data.result.html.notification);
                    },
                });
            });
        },
        registerWatchlistModal: function() {
            $(document).on('click', '.watchlist-add-modal', function() {
                var btn = $(this);
                $('#watchlistModal-' + btn.data('id')).remove();
                $.ajax({
                    url: $('#watchlist-add-modal-' + btn.data('id')).data('watchlistShowModalAddAction'),
                    success: function(data, textStatus, jqXHR) {
                        $('body').append(data.result.html.modal);
                        $('#watchlistModal-' + data.result.html.id).modal('toggle');
                    },
                });
            });

            $(document).on('click', '.watchlist-show-modal', function() {
                $('#watchlistModal').remove();
                $.ajax({
                    url: $('.watchlist-show-modal').data('watchlistShowModalAction'),
                    success: function(data, textStatus, jqXHR) {
                        $('body').append(data.result.html);
                        $('#watchlistModal').modal('toggle');
                    },
                });
            });
        },
        registerDelete: function() {
            $(document).on('click', '.watchlist-delete-item', function() {
                var btn = $(this);
                $.ajax({
                    url: $('#watchlist-delete-item-' + btn.data('id')).data('watchlistDeleteAction'),
                    success: function(data, textStatus, jqXHR) {
                        $('#watchlist-add-' + data.result.html.id).removeClass('watchlist-delete-item watchlist-added');
                        $('#watchlist-add-' + data.result.html.id).addClass('watchlist-add');
                        Watchlist.showNotification(data.result.html.notification);
                        Watchlist.watchlistUpdate();
                    },
                });
            });
        },
        registerDeleteAll: function() {
            $(document).on('click', '.watchlist-delete-all-button', function() {
                $.ajax({
                    url: $('.watchlist-delete-all-button').data('watchlistDeleteAllAction'),
                    success: function(data, textStatus, jqXHR) {
                        $('.watchlist-added').addClass('watchlist-add');
                        $('.watchlist-added').removeClass('watchlist-delete-item watchlist-added');
                        Watchlist.showNotification(data.result.html.notification);
                        Watchlist.watchlistUpdate();
                    },
                });
            });
        },
        watchlistUpdate: function() {
            $('.watchlist-loader').show();
            $.ajax({
                url: $('.watchlist-show-modal').data('watchlistUpdateAction'),
                success: function(data, textStatus, jqXHR) {
                    $('.watchlist-body').replaceWith(data.result.html);
                    $('.watchlist-download-link-href').html('&nbsp;');
                    $('.watchlist-download-link-text').removeClass('active');
                    $('.watchlist-loader').hide();
                },
            });
        },
        registerWatchlistSelect: function() {
            $(document).on('change', '#watchlist-selector', function() {
                $.ajax({
                    url: $('#watchlist-selector').data('watchlistSelectAction') + '&id=' + $('#watchlist-selector').find(':selected').val(),
                    success: function(data, textStatus, jqXHR) {
                        Watchlist.watchlistUpdate();
                    },
                });
            });
        },
        registerDownloadLink: function() {
            $(document).on('click', '.watchlist-download-link-button', function() {
                $.ajax({
                    url: $('.watchlist-download-link-button').data('watchlistDownloadLinkAction'),
                    success: function(data, textStatus, jqXHR) {
                        if (data.result.html !== false) {
                            $('.watchlist-download-link-href').attr('href', data.result.html);
                            $('.watchlist-download-link-href').html(data.result.html);
                            $('.watchlist-download-link-text').addClass('active');
                        }
                    },
                });
            });
        },
    };

    $(document).ready(function() {
        Watchlist.onReady();
    });

})(jQuery);
