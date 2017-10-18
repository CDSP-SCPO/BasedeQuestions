/**
 * 
 * Gathers the scripts for the variable view UI.
 * Relies on the given xHTML markup.
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet CDSP
 * @requires jQuery
 * @requires jQuery cookie
 */
var variableView = {

	/**
	 * @var String
	 */
	'emptyQuestionLitteral':null,

	/**
	 * @var String
	 */
	'questionNavigationUrl':null,

	/**
	 * @constructor
	 * @param {Object} params
	 */
	'init': function(params)
	{
		this.emptyQuestionLitteral = params.emptyQuestionLitteral;
		this.questionNavigationUrl = params.questionNavigationUrl;
		this._addCartListeners();		
		this._addTogglerListeners();

		if ( ! this.emptyQuestionLitteral)
		{
			this._addQuestionNavigationListener();
		}

		return this;
	},

	'_addQuestionNavigationListener': function(){
		$('span.questionNavigation').live('click', function(event){
			var url = variableView.questionNavigationUrl, variables = $(this).attr('id'), rndBox = $(this).closest('div.rndBox');
			variables = variables.split('_');
			url = url.replace(/vTo/, variables[2]);
			rndBox.addClass('updating');

			if (variables[1] == variables[2])
			{
				rndBox.find('.b1c').first().remove();
				rndBox.find('.b1c').first().show();
				rndBox.removeClass('updating');
				rndBox.removeClass('focused');
				return;
			}

			$.get(
				url,
				function(data)
				{
					if( ! rndBox.hasClass('focused'))
					{
							rndBox.addClass('focused');
					}
					
					if (rndBox.find('.b1c').length == 2)
					{
						rndBox.find('.b1c').first().remove();
						rndBox.find('.b1t').after(data);
						rndBox.removeClass('updating');
					}

					else
					{
						rndBox.find('.b1c').hide();
						rndBox.find('.b1t').after(data);
						rndBox.removeClass('updating');
					}

					$('span.cart').next().text($('span.cart').attr('title'));
					$('span.cart').next().click(function(){
						$(this).prev().trigger('click');
					});

				}
			);
		});
	},
	
	'_addTogglerListeners': function() {
		$('table.more, #showModalities').live('click', function(){
			var table = $('table.more');
			
			if (table.hasClass('toggled'))
			{
				table.removeClass('toggled');
			}
	
			else
			{
				table.addClass('toggled');
			}
	
			BDQGrips.resizeGrips();
		});
		$('ul.more').live('click', function(){
	
			if ($(this).hasClass('toggled'))
			{
				$(this).removeClass('toggled');
			}
	
			else
			{
				$(this).addClass('toggled');
			}
	
			BDQGrips.resizeGrips();
		});
	},
	
	'_addCartListeners': function() {
		$('span.cart').live('click', function(){
			$(this).next().text($(this).attr('title'));
		});
		$('span.cart').next().text($('span.cart').attr('title'));
		$('span.cart').next().click(function(){
			$(this).prev().trigger('click');
		});
	}

};