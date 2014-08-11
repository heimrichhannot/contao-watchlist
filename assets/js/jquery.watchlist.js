(function ($) {

    var Watchlist = {
        onReady: function () {
            this.initActions()
        },
        initActions : function(){

            // delegate on body, links will be added to dom and need to be served as well via ajax
            $('body').on('click', 'a[data-action="watchlist-update"]', function(e){
                e.preventDefault();

                var $this = $(this);

                $.ajax({
                    url: this.href,
                    dataType: 'json'
                }).done(function(data){
                    $($this.data('update')).html(data);
                });
            });
        }
    }


    $(document).ready(function(){
        Watchlist.onReady();
    });

})(jQuery);