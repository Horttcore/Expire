jQuery(document).ready(function(){

	Plugin = {

		init:function(){

			// Cache
			Plugin.expireField = jQuery('#expiration-date');
			Plugin.fieldWrapper = jQuery('#expiringdatediv');
			Plugin.editFieldButton = jQuery('.edit-expiringdate');
			Plugin.confirmButton = jQuery('.set-expiringdate');
			Plugin.cancelFieldButton = jQuery('.cancel-expiringdate');
			Plugin.date = jQuery('.setexpiringdate');
			Plugin.prevDate = Plugin.expireField.val();

			// Go
			Plugin.bindings();

		},

		bindings:function(){

			// Datepicker
			Plugin.expireField.datetimepicker(Expire.pickerConf);
			Plugin.expireField.change(function(){
				if ( '' === Plugin.expireField.val() )
					Plugin.date.html(Expire.never);
				else
					Plugin.date.html(Plugin.expireField.val());
			});

			// Show
			Plugin.editFieldButton.click(function(e){
				e.preventDefault();
				Plugin.fieldWrapper.toggle();
			});

			// Ok
			Plugin.confirmButton.click(function(e){
				e.preventDefault();
				Plugin.prevDate = Plugin.expireField.val();
				Plugin.fieldWrapper.hide();
			});

			// Cancel
			Plugin.cancelFieldButton.click(function(e){
				Plugin.fieldWrapper.hide();
				Plugin.date.html(Plugin.prevDate);
				Plugin.expireField.val(Plugin.prevDate);
			});

		}

	};

	Plugin.init();

});
