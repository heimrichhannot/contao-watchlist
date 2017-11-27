(function($) {

    var Watchlist = {
        onReady: function() {
            this.registerAdd();
            this.registerMultipleAdd();
            this.registerDelete();
            this.registerDeleteAll();
            this.registerWatchlistModal();
            this.registerWatchlistSelect();
            this.registerDownloadAll();
            this.registerDownloadLink();
            this.registerMultipleSelectAdd();
            this.registerDownloadItem();
            this.registerSlick();
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
                        Watchlist.watchlistUpdate();
                    }
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
                        Watchlist.watchlistUpdate();
                    }
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
                        Watchlist.watchlistUpdate();
                    }
                });
            });
        },
        registerWatchlistModal: function() {
            $(document).on('click', '.watchlist-add-modal', function() {
                $('#watchlistModal-' + $(this).data('id')).modal('toggle');
                Watchlist.updateModalSelect($(this).data('id'));
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
                    }
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
                    }
                });
            });
        },
        watchlistUpdate: function() {
            $('.watchlist-loader').show();
            $.ajax({
                url: $('.watchlist-toggle').data('watchlistUpdateAction'),
                success: function(data, textStatus, jqXHR) {
                    $('.watchlist-body').replaceWith(data.result.html);
                    Watchlist.initSlick();
                    $('.watchlist-loader').hide();
                }
            });
        },
        registerWatchlistSelect: function() {
            $(document).on('change', '#watchlist-selector', function() {
                $.ajax({
                    url: $('#watchlist-selector').data('watchlistSelectAction') + '&id=' + $('#watchlist-selector').find(':selected').val(),
                    success: function(data, textStatus, jqXHR) {
                        Watchlist.watchlistUpdate();
                        $('.watchlist-download-link-href').html('&nbsp;');
                        $('.watchlist-download-link-text').removeClass('active');
                    }
                });
            });
        },
        updateModalSelect: function(id) {
            $.ajax({
                url: $('#watchlist-select-input-' + id).data('watchlistUpdateModalAction') + '&id=' + id,
                success: function(data, textStatus, jqXHR) {
                    $('.watchlist-select-action-' + id).html(data.result.html);
                }
            });
        },
        registerDownloadAll: function() {
            $(document).on('click', '.watchlist-download-all-button', function() {
                $.ajax({
                    url: $('.watchlist-download-all-button').data('watchlistDownloadAllAction'),
                    success: function(data, textStatus, jqXHR) {
                        if (data.result.html !== false) {
                            window.open(data.result.html);
                        }
                    }
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
                    }
                });
            });
        },
        registerDownloadItem: function() {
            $(document).on('click', '.watchlist-download-item-button', function() {
                var btn = $(this);
                $.ajax({
                    url: $('#watchlist-download-item-' + btn.data('id')).data('watchlistDownloadItemAction'),
                    success: function(data, textStatus, jqXHR) {
                        var link = document.createElement('a');
                        link.href = data.result.html;
                        link.download = data.result.html.substr(data.result.html.lastIndexOf('/') + 1);
                        link.click();
                    }
                });
            });
        },
        registerSlick: function() {
            $('#watchlist').on('shown.bs.collapse', function() {
                $('.watchlist-item-image-list').slick('setPosition');
            });
            $(document).ready(function() {
                Watchlist.initSlick();
            });
        },
        initSlick: function() {
            if ($('.watchlist-list-item').length > 4) {
                $('.watchlist-item-image-list').slick({
                    infinite: true,
                    dots: true,
                    slidesToShow: 4,
                    slidesToScroll: 4,
                    responsive: [
                        {
                            breakpoint: 1024,
                            settings: {
                                slidesToShow: 3,
                                slidesToScroll: 3,
                                infinite: true,
                                dots: true
                            }
                        },
                        {
                            breakpoint: 600,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 2
                            }
                        },
                        {
                            breakpoint: 480,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        }
                    ]
                });
            }
        }
    };

    $(document).ready(function() {
        Watchlist.onReady();
    });

})(jQuery);
