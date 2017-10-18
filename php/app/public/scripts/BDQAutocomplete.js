/**
 * 
 * An autocomplete that takes the targeted fields and the chosen language into account.
 * Relies on a specific xHTML markup and some CSS.
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet CDSP
 * @param {Object} A list of options : termSeparator (response term separator), translate (Object with atLeast and results), acUrl : client side auto-complete base URL
 * @requires jQuery
 * @requires jQuery UI autocomplete
 */
jQuery.fn.BDQAutocomplete = function(params)
{
	
	var isOpened = false, 
		inp = this,
		searchForm = inp.closest('form'),
		searchQuestion = searchForm.find('.searchQuestion'),
		searchModalities = searchForm.find('.searchModalities'),
		searchVariableLabel = searchForm.find('.searchVariableLabel'),
		stemming = params.stemming,
		termSeparator = params.termSeparator,
		translate = params.translate,
		acUrl = params.acUrl,
		searchLang;

	searchLang = searchForm.find('input[type=radio][name=searchLang]:checked').attr('value');
	
	$('.radioSearchLang').change(function(){
		searchLang = searchForm.find('input[type=radio][name=searchLang]:checked').attr('value');
	});
	
	if ( ! searchLang)
	{
		searchLang = searchForm.find('.searchLang').val();
	}

	this.autocomplete(
	{
		'source': function(request, response)
		{
			if ( ! searchQuestion.attr('checked') &&	! searchModalities.attr('checked') && ! searchVariableLabel.attr('checked'))
			{
				response([]);
			}

			var url = acUrl, addToUrl = '';

			addToUrl += '?terms=' + escape(request.term) + termSeparator;
			addToUrl += '&searchQuestion=';
			addToUrl += searchQuestion.attr('checked') ? "1" : "0";
			addToUrl += '&searchModalities=';
			addToUrl += searchModalities.attr('checked') ? "1" : "0";
			addToUrl += '&searchVariableLabel=';
			addToUrl += searchVariableLabel.attr('checked') ? "1" : "0";
			addToUrl += '&searchLang=';
			addToUrl +=  searchLang;
			url = url + encodeURI(addToUrl);

			$.getJSON(
				url,
				function(data)
				{
					var i = 0, requestTerm = request.term, suggestions = [], elt, label, d;

					for (i = 0; i < data.length; i+=2)
					{
						elt = data[i];
						label = '<span class="label">' + elt.substring(0, requestTerm.length + (d = (elt.match(/\s/) !== null) ? 1 : 0)) + '<b>' + elt.substring(requestTerm.length + d, elt.length) + '</b></span>' ;

						if (data[i + 1] > 0)
						{
							label += ' - <span class="results">';

							if (stemming)
							{
								label += translate.atLeast;
							}

							
							label += + data[i + 1];
							
							label += translate.results;

							label += ' </span>';
						}

						suggestions.push({
							'id': i,
							'label': label,
							'value': elt
						});
					}

					response(suggestions);
				}
			);				
		},
		
		/**
		 * The number of items to display
		 */
		'max': 10,
		
		/**
		 * The number of caracters to type before suggestions appear.
		 */
		'minLength': 1,
		
		/**
		 * Open autocomplete callback
		 */
		'open':	function(event, ui) {
			isOpened = true;

			if ($.browser.msie && parseInt($.browser.version, 10) <= 6)
			{									
				$('#sortSelect').css('visibility', 'hidden');
			}
			
		},
		/**
		 * Close autocomplete callback
		 */
		'close': function(event, ui) {
			isOpened = false;
			$('#sortSelect').css('visibility', 'visible');

			if ($.browser.msie && parseInt($.browser.version, 10) == 6)
			{									
				$('#sortSelect').css('visibility', 'visible');
			}
			
		}
		
	});

	$(window).bind('resize', function() {

		if (isOpened)
		{
			inp.autocomplete("close");
			inp.autocomplete("search");
		}

	});
};
