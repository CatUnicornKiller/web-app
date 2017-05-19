(function($, undefined) {

$.nette.ext('spinner', {
	start: function () {
		loading();
	},
	complete: function () {
		remove_loading();
	}
});

})(jQuery);
