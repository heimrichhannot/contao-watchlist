(function ($) {

    var Watchlist = {
        onReady: function () {
            this.initActions();
        },
        initActions : function(){

            var notifyTimout = 0;

            // delegate on body, links will be added to dom and need to be served as well via ajax
            $('body').on('click', 'a[data-action="watchlist-update"]', function(e){
                e.preventDefault();

                var $this = $(this),
                    $parent = $($this.data('notify')).parent('.mod_watchlist');

                $.ajax({
                    url: this.href,
                    dataType: 'json'
                }).done(function(data){
                    clearTimeout(notifyTimout);

                    $($this.data('notify')).html(data.notification).addClass('in' +
                        '' +
                        '' +
                        '' +
                        '');

                    notifyTimout = window.setTimeout(function(){
                        $($this.data('notify')).removeClass('in');
                    }, 3500);

                    $($this.data('notify')).html(data.notification);
                    $($this.data('update')).html(data.watchlist);
                    $($this.data('badge')).html(data.count);

                    // update parent css class & count
                    $parent.find('#watchlist-wrapper').data('count', data.count).attr('class', data.cssClass);

                    // some manupalations based on the action
                    if(data.action == 'add'){
                        $this.addClass('active');
                    }

                    if(data.action == 'deleteAll'){
                        $('.watchlist-add').removeClass('active');
                    }

                });
            });
        }
    }


    $(document).ready(function(){
        Watchlist.onReady();
    });

})(jQuery);
