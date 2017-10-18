/**
 * Gathers the script for the left side menu UI.
 * Relies on the given xHTML markup.
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet
 * @requires jQuery
 * @requires jQuery UI Sortable
 * @requires jQuery cookie
 * @requires DataTable
 * @requires LiveQuery
 */
var dynamicFacets = {
	/**
	 * @var Boolean
	 */
	'displayConcept': null,
	/**
	 * @var Int
	 */
	'numFound': null,
	/**
	 * @var Int
	 */
	'keywordFiltersCount': null,
	/**
	 * @var String
	 */
	'updateFacetRoute': null,
	/**
	 * @var Int
	 */
	'maxFacetDisplay': null,
	/**
	 * @var Array an array of datatable objects
	 */
	'tables': null,
	/**
	 * @var Object a datatable object
	 */
	'domainTable': null,
	/**
	 * @var Object a datatable object
	 */
	'studySerieTable': null,
	/**
	 * @var Object a datatable object
	 */
	'studyTable': null,
	/**
	 * @var Object a datatable object
	 */
	'decadeTable': null,
	/**
	 * @var Object a datatable object
	 */
	'conceptTable': null,
	/**
	 * @var Object a datatable object
	 */
	'queryFilterTable': null,
	/**
	 * @var Object
	 */
	'translate': null,
	/**
	 * @var Int
	 */
	'searchQuestion': null,
	/**
	 * @var Int
	 */
	'searchModalities': null,
	/**
	 * @var Int
	 */
	'searchVariable': null,
	/**
	 * @constructor
	 * @param {Object} params A lot of things, like the translation strings, the user settings, the current URL, etc.
	 * @type Object
	 */
	'init': function(params)
	{
		this.displayConcept = params.displayConcept;
		this.numFound = params.numFound;
		this.keywordFiltersCount = params.keywordFiltersCount;
		this.updateFacetRoute = params.updateFacetRoute;
		this.maxFacetDisplay = params.maxFacetDisplay;
		this.searchQuestion = params.searchQuestion;
		this.searchModalities = params.searchModalities;
		this.searchVariable = params.searchVariable;
		this.translate = {
			'kwFs1': params.translate.kwFs1,
			'kwFs2': params.translate.kwFs2,
			'kwFs3': params.translate.kwFs3,
			'keywordFiltersEnter': params.translate.keywordFiltersEnter
		};

		if (this.numFound > 0)
		{
			dynamicFacets._initDomainTable(params);
			dynamicFacets._initStudySerieTable(params);
			dynamicFacets._initStudyTable(params);
			dynamicFacets._initDecadeTable(params);
			dynamicFacets._initQueryFilterTable(params);
			dynamicFacets.tables = [dynamicFacets.domainTable, dynamicFacets.studySerieTable, dynamicFacets.studyTable, dynamicFacets.decadeTable, dynamicFacets.queryFilterTable];

			if (dynamicFacets.displayConcept)
			{
				dynamicFacets._initConceptTable(params);
				dynamicFacets.tables.push(dynamicFacets.conceptTable);
			}

			dynamicFacets.updateFacets();
		}

		this._addSubmitListener();
		this._addTogglersListener();
		this._addQueryFilterAddListener();
		this._makeSortable();
		this._addFacetsListener();
		this._addFacetsResetListener();
		this._addQueryFilterTargetListener();
		this._addQueryFilterInputListener();
		this._preventFFAC();
		return this;
	},
	
	'_addQueryFilterInputListener': function()
	{
		$('#queryFilterInput').focus(function(){
			
			if ($(this).val() == dynamicFacets.translate.keywordFiltersEnter)
			{
				$(this).val('');
			}
			
		})
			.val(dynamicFacets.translate.keywordFiltersEnter);
	},

	/**
	 * @param {object} params
	 */
	'_initDomainTable': function(params)
	{
		this.domainTable = $('#domainFilters').dataTable(
		{
			"sDom": 'lf<"clear">rtip<"clear">',
			"aoColumns": [{"sSortDataType": "dom-checkbox"},{"sSortDataType": "text"},{"sSortDataType": "number"},{"bVisible": false}],
			"oLanguage": {
				"sLengthMenu": params.translate.table._common.sLengthMenu,
				"sZeroRecords": params.translate.table.domain.sZeroRecords,
				"sInfo": params.translate.table.domain.sInfo,
				"sInfoEmpty": params.translate.table.domain.sInfoEmpty,
				"sInfoFiltered": params.translate.table.domain.sInfoFiltered,
				"sSearch" : params.translate.table._common.sSearch,
				"oPaginate": params.translate.table._common.oPaginate
			},
			"aaSorting": [[0, "desc"], [ 2, "desc" ]],
			"sPaginationType": "input",
			"fnInitComplete": function(oSettings) {

				if (oSettings.fnRecordsDisplay() <= dynamicFacets.maxFacetDisplay)
				{
					$('#domainFilters_length').hide();
					$('#domainFilters_filter').hide();
					$('#domainFilters_paginate').hide();
					$('#domainFilters_info').hide();
				}

			},
			"aLengthMenu":[5,10,25],
			"iDisplayLength":dynamicFacets.maxFacetDisplay,
			"fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull)
			{
				$(nRow).css("display", "");
				return nRow;
			}
		});
	},

	/**
	 * @param {object} params
	 */
	'_initStudySerieTable': function(params)
	{
		this.studySerieTable = $('#studySerieFilters').dataTable(
		{
			"sDom": 'lf<"clear">rtip<"clear">',
			"aoColumns": [{"sSortDataType": "dom-checkbox"},{"sSortDataType": "text"},{"sSortDataType": "number"},{"bVisible": false}],
			"oLanguage": {
				"sLengthMenu": params.translate.table._common.sLengthMenu,
				"sZeroRecords": params.translate.table.studySerie.sZeroRecords,
				"sInfo": params.translate.table.studySerie.sInfo,
				"sInfoEmpty": params.translate.table.studySerie.sInfoEmpty,
				"sInfoFiltered": params.translate.table.studySerie.sInfoFiltered,
				"sSearch" : params.translate.table._common.sSearch,
				"oPaginate": params.translate.table._common.oPaginate
			},
			"aaSorting": [[0, "desc"], [ 2, "desc" ]],
			"sPaginationType": "input",
			"fnInitComplete": function(oSettings)
			{

				if (oSettings.fnRecordsDisplay() <= dynamicFacets.maxFacetDisplay)
				{
					$('#studySerieFilters_length').hide();
					$('#studySerieFilters_filter').hide();
					$('#studySerieFilters_paginate').hide();
					$('#studySerieFilters_info').hide();
				}

			},
			"aLengthMenu":[5,10,25],
			"iDisplayLength":dynamicFacets.maxFacetDisplay,
			"fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull)
			{
				$(nRow).css("display", "");
				return nRow;
			}
		});
	},

	/**
	 * @param {object} params
	 */
	'_initStudyTable': function(params)
	{
		this.studyTable = $('#studyFilters').dataTable(
		{
			"sDom": 'lf<"clear">rtip<"clear">',
			"aoColumns": [{"sSortDataType": "dom-checkbox"},{"sSortDataType": "text"},{"sSortDataType": "number"},{"bVisible": false}],
			"oLanguage": {
				"sLengthMenu": params.translate.table._common.sLengthMenu,
				"sZeroRecords": params.translate.table.study.sZeroRecords,
				"sInfo": params.translate.table.study.sInfo,
				"sInfoEmpty": params.translate.table.study.sInfoEmpty,
				"sInfoFiltered": params.translate.table.study.sInfoFiltered,
				"sSearch" : params.translate.table._common.sSearch,
				"oPaginate": params.translate.table._common.oPaginate
			},
			"aaSorting": [[0, "desc"], [ 2, "desc" ]],
			"sPaginationType": "input",
			"fnInitComplete": function(oSettings) {

				if (oSettings.fnRecordsDisplay() <= dynamicFacets.maxFacetDisplay)
				{
					$('#studyFilters_length').hide();
					$('#studyFilters_filter').hide();
					$('#studyFilters_paginate').hide();
					$('#studyFilters_info').hide();
				}

			},
			"aLengthMenu":[5,10,25],
			"iDisplayLength":dynamicFacets.maxFacetDisplay,
			"fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull)
			{
				$(nRow).css("display", "");
				return nRow;
			}
		});
	},

	/**
	 * @param {object} params
	 */
	'_initDecadeTable': function(params)
	{
		this.decadeTable = $('#decadeFilters').dataTable(
		{
			"sDom": 'lf<"clear">rtip<"clear">',
			"aoColumns": [{"sSortDataType": "dom-checkbox"},{"sSortDataType": "text"},{"sSortDataType": "number"}],
			"oLanguage": {
				"sLengthMenu": params.translate.table._common.sLengthMenu,
				"sZeroRecords": params.translate.table.decade.sZeroRecords,
				"sInfo": params.translate.table.decade.sInfo,
				"sInfoEmpty": params.translate.table.decade.sInfoEmpty,
				"sInfoFiltered": params.translate.table.decade.sInfoFiltered,
				"sSearch" : params.translate.table._common.sSearch,
				"oPaginate": params.translate.table._common.oPaginate
			},
			"aaSorting": [[0, "desc"], [ 1, "desc" ]],
			"sPaginationType": "input",
			"fnInitComplete": function(oSettings) {
				
				if (oSettings.fnRecordsDisplay() <= dynamicFacets.maxFacetDisplay)
				{
					$('#decadeFilters_length').hide();
					$('#decadeFilters_filter').hide();
					$('#decadeFilters_paginate').hide();
					$('#decadeFilters_info').hide();
				}

			},
			"aLengthMenu":[5,10,25],
			"iDisplayLength":dynamicFacets.maxFacetDisplay,
			"fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull)
			{
				$(nRow).css("display", "");
				return nRow;
			}
		});
	},

	/**
	 * @param {object} params
	 */
	'_initConceptTable': function(params)
	{
		this.conceptTable = $('#conceptFilters').dataTable(
		{
			"sDom": 'lf<"clear">rtip<"clear">',
			"aoColumns": [{"sSortDataType": "dom-checkbox"},{"sSortDataType": "text"},{"sSortDataType": "number"},{"bVisible": false}],
			"oLanguage": {
				"sLengthMenu": params.translate.table._common.sLengthMenu,
				"sZeroRecords": params.translate.table.decade.sZeroRecords,
				"sInfo": params.translate.table.decade.sInfo,
				"sInfoEmpty": params.translate.table.decade.sInfoEmpty,
				"sInfoFiltered": params.translate.table.decade.sInfoFiltered,
				"sSearch" : params.translate.table._common.sSearch,
				"oPaginate": params.translate.table._common.oPaginate
			},
			"aaSorting": [[0, "desc"], [ 2, "desc" ]],
			"sPaginationType": "input",
			"fnInitComplete": function(oSettings) {

				if (oSettings.fnRecordsDisplay() <= dynamicFacets.maxFacetDisplay)
				{
					$('#conceptFilters_length').hide();
					$('#conceptFilters_filter').hide();
					$('#conceptFilters_paginate').hide();
					$('#conceptFilters_info').hide();
				}

			},
			"aLengthMenu":[5,10,25],
			"iDisplayLength":dynamicFacets.maxFacetDisplay,
			"fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull)
			{
				$(nRow).css("display", "");
				return nRow;
			}
		});
	},
	
	/**
	 * @param {object} params
	 */
	'_initQueryFilterTable': function(params)
	{
		this.queryFilterTable = $('#queryFilters').dataTable(
		{
			"sDom": 'lf<"clear">rtip<"clear">',
			"aoColumns": [{"sSortDataType": "dom-checkbox"},{"sSortDataType": "text"},{"sSortDataType": "dom-checkbox"},{"sSortDataType": "dom-checkbox"},{"sSortDataType": "dom-checkbox"},{"sSortDataType": "number"}],
			"oLanguage": {
				"sLengthMenu": params.translate.table._common.sLengthMenu,
				"sZeroRecords": params.translate.table.queryFilter.sZeroRecords,
				"sInfo": params.translate.table.queryFilter.sInfo,
				"sInfoEmpty": params.translate.table.queryFilter.sInfoEmpty,
				"sInfoFiltered": params.translate.table.queryFilter.sInfoFiltered,
				"sSearch" : params.translate.table._common.sSearch,
				"oPaginate": params.translate.table._common.oPaginate
			},
			"aaSorting": [[0, "desc"], [5, "desc" ]],
			"sPaginationType": "input",
			"fnInitComplete": function(oSettings) {

				if (oSettings.fnRecordsDisplay() <= dynamicFacets.maxFacetDisplay)
				{
					$('#queryFilters_length').hide();
					$('#queryFilters_filter').hide();
					$('#queryFilters_paginate').hide();
					$('#queryFilters_info').hide();
				}

			},
			"aLengthMenu":[5,10,25],
			"iDisplayLength":dynamicFacets.maxFacetDisplay,
			"fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull)
			{
				$(nRow).css("display", "");
				return nRow;
			}
		});
	},
	
	'_makeSortable': function()
	{
		$("#sortable").sortable({
			axis: 'y',
			cursor: 'move',
			handle:'div.facetHeader',
			stop: function(event, ui){
				var i = 0;
				$(this).find('li').each(function(){
					$.cookie('settings[' + $(this).attr('id') + 'Position]', i, { expires: 10000, path: '/' });
					i++;
				});
			},
			tolerance: 'pointer',
			placeholder : 'sortableLandmark',
			start: function(event, ui)
			{
				$('.sortableLandmark').height(ui.item.height());
			}
		});
	},

	/**
	 * @param {Object} fromCB a jQuery object wrapping the clicked checkbox
	 */
	'updateFacets': function(fromCB)
	{
		var selectedDomains = [], selectedConcepts = [], selectedStudySeries = [], selectedDecades = [], queryFilters = [], selectedStudies = [];
		var url = this.updateFacetRoute, addToUrl = '';
		var type;
		var count = 0;

		$(this.domainTable.fnGetNodes()).find(("input.domainFilter:checked")).each(
			function() {
				selectedDomains.push($(this).val());
				count++;
			}
		);

		if (this.displayConcept)
		{
			$(this.conceptTable.fnGetNodes()).find("input.conceptFilter:checked").each(
				function() {
					selectedConcepts.push($(this).val());
					count++;
				}
			);
		}

		$(this.studySerieTable.fnGetNodes()).find("input.studySerieFilter:checked").each(
			function() {
				selectedStudySeries.push($(this).val());
				count++;
			}
		);

		$(this.decadeTable.fnGetNodes()).find("input.decadeFilter:checked").each(
			function() {
				selectedDecades.push($(this).val());
				count++;
			}
		);

		$(this.studyTable.fnGetNodes()).find("input.studyFilter:checked").each(
			function() {
				selectedStudies.push($(this).val());
				count++;
			}
		);

		$(this.queryFilterTable.fnGetNodes()).find("input.queryFilter").each(
			function() {
				var val = $(this).val(), tr = $(this).closest('tr'), target = 0;
				target += tr.find('input.qCb').attr('checked') ? dynamicFacets.searchQuestion : 0;
				target += tr.find('input.cCb').attr('checked') ? dynamicFacets.searchModalities : 0;
				target += tr.find('input.vCb').attr('checked') ? dynamicFacets.searchVariable : 0;
				
				if (target !== 0)
				{
					val += target;
					val += ($(this).attr('checked') && ++count) ? "1" : "0";
					queryFilters.push(encodeURI(val));
				}

			}
		);
		
		if (selectedDomains.length > 0)
		{
			addToUrl += '&domainFilters[]=';
			addToUrl += selectedDomains.join('&domainFilters[]=');
		}
	
		if (selectedConcepts.length > 0)
		{
			addToUrl += '&conceptFilters[]=';
			addToUrl += selectedConcepts.join('&conceptFilters[]=');
		}
		
		if (selectedStudySeries.length > 0)
		{
			addToUrl += '&studySerieFilters[]=';
			addToUrl += selectedStudySeries.join('&studySerieFilters[]=');
		}
		
		if (selectedDecades.length > 0)
		{
			addToUrl += '&decadeFilters[]=';
			addToUrl += selectedDecades.join('&decadeFilters[]=');
		}
	
		if (queryFilters.length > 0)
		{
			addToUrl += '&queryFilters2[]=';
			addToUrl += queryFilters.join('&queryFilters2[]=');
		}
	
		if (selectedStudies.length > 0)
		{
			addToUrl += '&studyFilters[]=';
			addToUrl += selectedStudies.join('&studyFilters[]=');
		}
	
		if (count === 0)
		{
			$('span.facetReset').css('display', 'none');
		}
	
		else
		{
			$('span.facetReset').css('display', 'inline-block');
		}
	
		url = url + encodeURI(addToUrl);
	
		$.getJSON(
			url,
			function(data)
			{

				if ( // The current choice doesn't return any result
						fromCB // Not an automatic update
						&&
						data.numFound === 0
						&& // This can happen only if:
						(
							fromCB.hasClass('queryFilter') //The user added a query filter 
							||
							fromCB.hasClass('qfTarget') //The user changed the query filter target
						)
						
				)
				{
					var qfCb;
					// The checkbox that makes all results equal to 0 has to be unchecked
					if (fromCB.hasClass('qfTarget'))
					{
						qfCb = fromCB.closest('tr').find('input.queryFilter');
					}
					
					else
					{
						qfCb = fromCB;
					}

					qfCb.removeAttr('checked'); //It is unchecked
					dynamicFacets.updateFacets(fromCB);
					return;
				}
				
				dynamicFacets.updateTable(data.studies, dynamicFacets.studyTable, 'study', ! (fromCB && fromCB.hasClass('studyFilter')));
				dynamicFacets.updateTable(data.domains, dynamicFacets.domainTable, 'domain', ! (fromCB && fromCB.hasClass('domainFilter')));
				dynamicFacets.updateTable(data.studySeries, dynamicFacets.studySerieTable, 'studySerie', ! (fromCB && fromCB.hasClass('studySerieFilter')));
				dynamicFacets.updateTable(data.decades, dynamicFacets.decadeTable, 'decade', ! (fromCB && fromCB.hasClass('decadeFilter')));

				if (dynamicFacets.displayConcept)
				{
					dynamicFacets.updateTable(data.concepts, dynamicFacets.conceptTable, 'concept', ! (fromCB && fromCB.hasClass('conceptFilter')));
				}
				
				dynamicFacets.updateQueryFilterTable(data.keywords, ! (fromCB && (fromCB.hasClass('queryFilter') || fromCB.hasClass('qfTarget'))), fromCB);

				if (fromCB && ! fromCB.hasClass('studyFilter'))
				{
					var toAnimate = '#studyFilters td.count';
					
					if ( ! fromCB.hasClass('conceptFilter'))
					{
						toAnimate += ', #conceptFilters td.count';
					}
					
					if ( ! fromCB.hasClass('queryFilter'))
					{
						toAnimate += ', #queryFilters td.count';
					}

					if ( ! fromCB.hasClass('studySerieFilter'))
					{
						toAnimate += ', #studySerieFilters td.count';
						
						if ( ! fromCB.hasClass('domainFilter'))
						{
							toAnimate += ', #domainFilters td.count';
							
							if ( ! fromCB.hasClass('decadeFilter'))
							{
								toAnimate += ', #decadeFilters td.count';
							}
							
						}

					}
					
					$(toAnimate).effect("pulsate", { times:1 }, 150);

				}

			}
		);
	
	},

	/**
	 * @param {Array} values the AJAX request facets values
	 * @param {Object} table the DataTable object
	 * @param {String} checkboxPrefix the table checkboxes prefix
	 * @param {Boolean} redraw redraw the table, resetting the pagination
	 */
	'updateTable': function(values, table, checkboxPrefix, redraw)
	{
		var nodes = $(table.fnGetNodes()), l = nodes.length;
		nodes.each(function(index, value){
			var id, count, tr = $(this), td = tr.find('td').first(), cb = td.find('input'), found = false;
	
			for (var i = 0 ; i < values.length ; i += 2)
			{
				id = values[i];
				count = values[i + 1];
	
				if ((checkboxPrefix + 'CheckBox_' + id) == cb.attr('id'))
				{
					found = true;
					break;
				}
	
			}
	
			if (found)
			{
	
				if (tr.hasClass('disabled'))
				{
					tr.removeClass('disabled');
				}
	
				cb.removeAttr('disabled');
			}
	
			else
			{
				count = 0;
	
				if ( ! tr.hasClass('disabled'))
				{
					tr.addClass('disabled');
				}
	
				cb.removeAttr('checked');
				cb.attr('disabled', 'true');
			}
	
			if ($.browser.msie && parseInt($.browser.version, 10) == 6)
			{
				table.fnUpdate(td.html(), tr[0], 0, false, false);
			}
	
			table.fnUpdate(count, tr[0], 2, false, index == (l - 1));
		});
	
		table.fnDraw(redraw);
	},

	/**
	 * @param {Object} data the AJAX request facet values
	 */
	'updateQueryFilterTable': function(data, redraw, fromCB)
	{
		var nodes = $(this.queryFilterTable.fnGetNodes()),
			l = nodes.length,
			qfCBId = false,
			update = false;

		if (fromCB && fromCB.hasClass('qfTarget')) // The current choice has results and a query filter target was clicked
		{
			qfCBId = fromCB.closest('tr').find('input.queryFilter').attr('id');
		}

		nodes.each(function(index, value){
			var id, count, 
			tr = $(this),
			td = tr.find('td').first(),
			cb = td.find('input'),
			found = false,
			qCb = tr.find('input.qCb'),
			cCb = tr.find('input.cCb'),
			vCb = tr.find('input.vCb'),
			target = 0;
			target += qCb.attr("checked") ? dynamicFacets.searchQuestion : 0;
			target += cCb.attr("checked") ? dynamicFacets.searchModalities : 0;
			target += vCb.attr("checked") ? dynamicFacets.searchVariable : 0;

			var div = $(this).closest('div'), val = cb.val(), count = data[val + target];
			
			if (count > 0)
			{
	
				if (tr.hasClass('disabled'))
				{
					tr.removeClass('disabled');
					cb.removeAttr('disabled');
				}
				
				if (qfCBId === cb.attr('id'))
				{

					if ( ! cb.attr('checked'))
					{
						update = true;
					}

					cb.attr('checked', 'checked');
					
				}

			}

			else
			{

				if ( ! tr.hasClass('disabled'))
				{
					tr.addClass('disabled');
				}

				cb.removeAttr('checked');
				cb.attr('disabled', 'true');
			}

			if (count === undefined)
			{
				count = '-';
			}

			if ($.browser.msie && parseInt($.browser.version, 10) == 6)
			{
				dynamicFacets.queryFilterTable.fnUpdate(td.html(), tr[0], 0, false, false);
			}

			dynamicFacets.queryFilterTable.fnUpdate(count, tr[0], 5, false, false);
		});

		this.queryFilterTable.fnDraw(redraw);

		if (update)
		{
			this.updateFacets(fromCB);
		}
	
	},

	'addQueryFilter': function()
	{
		var val = $('#queryFilterInput').val(), cb0, cb1, cb2, cb3, label, index, tr;
		$('#queryFilterInput').val('');
		val = jQuery.trim(val);

		if (val === '' || ! luceneQueryValidator.checkQuery(val))
		{
			return;
		}

		_val = val.replace(/(\S)\-(\S)/, "$1 $2");
		_val = $.normalize(_val);
		cb0 = '<input id="queryCheckbox_' + this.keywordFiltersCount + '" class="queryFilter resultFilter" name="queryFilters2[]" value="" type="checkbox" checked="checked">';
		label = '<label for="queryCheckbox_' + this.keywordFiltersCount + '">' + val + '</label>';
		cb1 = '<input class="qfTarget qCb" name="qQueryFilter_' + this.keywordFiltersCount + '" checked="checked" value="" type="checkbox">';
		cb2 = '<input class="qfTarget cCb" name="cQueryFilter_' + this.keywordFiltersCount + '" checked="checked" value="" type="checkbox">';
		cb3 = '<input class="qfTarget vCb" name="vQueryFilter_' + this.keywordFiltersCount + '" checked="checked" value="" type="checkbox">';
		index = this.queryFilterTable.fnAddData(
			[
			cb0,
			label,
			cb1,
			cb2,
			cb3,
			''
			],
			false
		);
		tr =  this.queryFilterTable.fnGetNodes(index);
		tr = $(tr);
		tr.find('td').eq(0).addClass('checkbox').find('input').val(_val);
		tr.find('td').eq(1).addClass('title');
		tr.find('td').eq(2).addClass('checkbox');
		tr.find('td').eq(3).addClass('checkbox');
		tr.find('td').eq(4).addClass('checkbox');
		tr.find('td').eq(5).addClass('count');
		this.queryFilterTable.fnDraw(false);
		this.keywordFiltersCount ++;

		if (this.keywordFiltersCount > dynamicFacets.maxFacetDisplay)
		{
			$('#queryFilters_length').show();
			$('#queryFilters_filter').show();
			$('#queryFilters_paginate').show();
			$('#queryFilters_info').show();
		}

		this.updateFacets(tr.find('input.queryFilter'));
		$('#queryFilterInput').val(this.translate.keywordFiltersEnter)
							.blur();
	},

	'resetFacetForm': function()
	{
		
		for (var i = 0; i < this.tables.length; i++)
		{
			$(this.tables[i].fnGetNodes()).find("input:checked").each(function(){
				$(this).removeAttr('checked');
			});
		}

	},
	
	'_addSubmitListener': function()
	{
		$('#facetFilter').submit(function(){
			$('#facetFilter div.toggled').hide();

			for (var i = 0; i < dynamicFacets.tables.length; i++)
			{
				dynamicFacets.tables[i].fnDestroy();
			}

			$('#queryFilters tbody tr').each(function(){
				var target = 0;
				var cb = $(this).find('input.queryFilter');
				target += $(this).find('input.qCb:checked').attr('checked') ? dynamicFacets.searchQuestion : 0;
				target += $(this).find('input.cCb:checked').attr('checked') ? dynamicFacets.searchModalities : 0;
				target += $(this).find('input.vCb:checked').attr('checked') ? dynamicFacets.searchVariable : 0;
				cb.val(cb.val() + target);
			});

		});
	},
	
	'_addTogglersListener': function()
	{
		$('span.toggler').click(function(event){
			
			if ( ! $(this).hasClass('down'))
			{
				$(this).addClass('down');
				$.cookie('settings[' + $(this).closest('li').attr('id') + 'Display]', "1", { expires: 10000, path: '/' });
			}

			else
			{
				$(this).removeClass('down');
				$.cookie('settings[' + $(this).closest('li').attr('id') + 'Display]', "0", { expires: 10000, path: '/' });
			}

			event.stopPropagation();
			var toggled = $(this).closest('div.facetHeader').next();
			toggled.toggle();
		});
	},

	'_addQueryFilterAddListener': function()
	{
		$('#facetFilter').bind("keypress", function(e) {
			
			if(e.keyCode == 13)
			{
				return false;
			}
	
		});

		$('#queryFilterInput').keyup(function(e) {

			if(e.keyCode == 13)
			{
				$('#firefoxFix').focus();//Firefox on Linux wannabe fix - seems to be broken
				dynamicFacets.addQueryFilter();
			}
			
		});

		$('#queryFilterButton').click(function(e) {
			dynamicFacets.addQueryFilter();
		});

		
	},

	'_addFacetsListener': function()
	{
		$('input.studyFilter, input.domainFilter, input.conceptFilter, input.studySerieFilter, input.decadeFilter, input.queryFilter').livequery('change',function(){
			dynamicFacets.updateFacets($(this));
		});
	},

	'_addFacetsResetListener': function()
	{
		$('span.facetReset').click(function(e){
			dynamicFacets.resetFacetForm();
			dynamicFacets.updateFacets();
		});
	},

	'_addQueryFilterTargetListener': function()
	{
		$('input.qfTarget').live('click', function(e){
			dynamicFacets.updateFacets($(this));
		});
	},
	
	'_preventFFAC': function()
	{

		if($.browser.mozilla)
		{
			$('#facetFilter').attr('autocomplete', 'off');
		}

	}

};
