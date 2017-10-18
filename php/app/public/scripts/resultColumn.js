/**
 * 
 * Gather the script for the result column UI.
 * Relies on the given xHTML markup.
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet CDSP
 * @requires jQuery
 * @requires jQuery cookie
 */
var resultColumn = {
	/**
	 * @var Boolean	is the navigation set on in the user settings ?
	 */
	'questionNavigation':null,
	/**
	 * @var String the question navigation url mask 
	 */
	'questionNavigationUrl':null,
	/**
	 * @var Object the translation strings
	 */
	'translate':null,
	/**
	 * @constructor
	 * @type Object
	 * @param {Object} params translate: Object, questionNavigation: boolean, questionNavigationUrl: String
	 */
	'init': function(params)
	{
		this.questionNavigation = params.questionNavigation;
		this.questionNavigationUrl = params.questionNavigationUrl;
		
		if (this.questionNavigation)
		{
			this._initQuestionNavigation();
		}
		
		this.translate = params.translate;
		this._initTogglers();
		this._initSortSelect();
		this._initDetails();
		return this;
	},

	'_initSortSelect': function(){
		$('#sortSelect').change(function(event){
			$(location).attr('href',$(this).val());
		});
	},
	
	'_initTogglers': function(){
		$('div.hideModa td.modalities, div.rndBox td.mrModa, div.rndBox td.mrItem').live('click', function(){

			if($(this).hasClass('toggled'))
			{
				$(this).closest('td').removeClass('toggled');
			}

			else
			{
				$(this).addClass('toggled');
			}

			BDQGrips.resizeGrips();

		});
		$('td.mrItem a').click(function(event){
			event.stopPropagation();
		});
	},
	
	'_initQuestionNavigation': function(){
		$('span.questionNavigation').live('click', function(event){
			var url = resultColumn.questionNavigationUrl, variables = $(this).attr('id'), rndBox = $(this).closest('div.rndBox');
			variables = variables.split('_');
			url = url.replace(/vFrom/, variables[1]);
			url = url.replace(/vTo/, variables[2]);
			url = url.replace(/vDdiFileId/, variables[3]);
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

					}
			);
		});
	},
	
	'_initDetails': function(){
		$('div.detail').click(function(){

			if ($('div.rightTube').hasClass('hideModa'))
			{
				$('div.rightTube').removeClass('hideModa');
				$.cookie("settings[displayModalities]", "1", { expires: 10000, path: '/' });
				BDQGrips.resizeGrips();
				$(this).html(resultColumn.translate.hideModalities2);
			}

			else
			{
				$('div.rightTube').addClass('hideModa');
				$.cookie("settings[displayModalities]", "0", { expires: 10000, path: '/' });
				$(this).html(resultColumn.translate.showModalities2);
			}
			
		});
	}
};