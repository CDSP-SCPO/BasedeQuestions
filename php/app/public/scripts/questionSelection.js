/**
 * 
 * Gathers the question selection features
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet CDSP
 * @requires jQuery
 * @requires dataTable
 * @requires jQuery cookie
 */
var questionSelection = {

	/**
	 * @var Object a Datatable objet
	 */
	'selectionTable' : null,

	/**
	 * @var Object the translation strings
	 */
	'translate': null,

	/**
	 * @var String
	 */
	'exportUrl': null,

	/**
	 * @var String
	 */
	'questionSelectionUrl':null,

	/**
	 * @var int the number of questions to display
	 */
	'maxQuestionDisplay': null,

	/**
	 * @var string
	 */
	'basketRemoveImgSrc': "/img/icons/basket_remove.png",
	
	/**
	 * @var string
	 */
	'xlsExportImgSrc': "/img/icons/xls_hover.png",
	
	/**
	 * @var string
	 */
	'csvExportImgSrc':"/img/icons/csv_hover.png",
	
	/**
	 * @constructor
	 * @param {object} params
	 */
	'init': function(params)
	{
		this.translate = params.translate;
		this.exportUrl = params.exportUrl;
		this.questionSelectionUrl = params.questionSelectionUrl;
		this.maxQuestionDisplay = params.maxQuestionDisplay;
		this._initTable();
		this._initExportAndClearBasketDiv();
		this._addBasketDeleteListener();
		this._addTogglerListener();
		return this;
	},
	
	'_initTable': function(){
		this.selectionTable = $('table.selection').dataTable({
			"sDom": 'l<"export">rtip',
			"aLengthMenu":[5,10,25],
			"iDisplayLength": this.maxQuestionDisplay,
			"aoColumns": [ null, null, null, {"bSortable": false, "bSearchable": false }, { "bSortable": false, "bSearchable": false } ],
			"oLanguage": 
			{
				"sLengthMenu": this.translate.table.lengthMenu,
				"sZeroRecords": this.translate.table.zeroRecord,
				"sInfo": this.translate.table.info,
				"sInfoEmpty": this.translate.table.infoEmpty,
				"sInfoFiltered": this.translate.table.infoFiltered,
				"sSearch" : this.translate.table.search,
				"oPaginate":{
					"sPrevious": this.translate.table.paginate.previous,
					"sNext": this.translate.table.paginate.next
				}
			},
			"fnInitComplete": function(oSettings) {
	
				if (oSettings.fnRecordsDisplay() <= this.maxQuestionDisplay)
				{
					$('.dataTables_length').hide();
					$('.dataTables_filter').hide();
					$('.dataTables_paginate').hide();
					$('.dataTables_info').hide();
				}
	
			},
			"sPaginationType": "input",
			"fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull)
			{
				$(nRow).css("display", "");
				return nRow;
			}
		});
	},
	
	'_initExportAndClearBasketDiv': function(){
		$('div.export').html(
			'<img src="' + this.basketRemoveImgSrc + '" id="clearCart" title="' + this.translate.clearBasket + '"/>'
			+ '<img src="' + this.csvExportImgSrc + '" id="exportCsv" title="' + this.translate.exportCsv + '"/>'
			+ '<img src="' + this.xlsExportImgSrc + '" id="exportXls" title="' + this.translate.exportXls + '"/>'
		);
		$('#exportCsv').click(function(){
			var url = questionSelection.exportUrl;
			url += '?&format=csv';
			$(location).attr('href', url);
		});
		$('#exportXls').click(function(){
			var url = questionSelection.exportUrl;
			url += '?&format=xlsx';
			$(location).attr('href', url);
		});
		$('#clearCart').click(function(){

			if (confirm(questionSelection.translate.clearBasketConfirm))
			{
				basket.clear();
				$(location).attr('href', questionSelection.questionSelectionUrl);
			}

		});
	},
	
	'_addBasketDeleteListener': function(){
		$('td.delete img').live('click',function(){
			var tr = $(this).closest('tr');
			basket.remove(tr.attr('id').split('_')[1]);
			questionSelection.selectionTable.fnDeleteRow(tr.get(0));
			var count = $('#topMenu span.count');
			var c = parseInt(count.text(), 10) - 1;
			count.text(c);
	
			if (c === 0 && $('#topMenu a.questionSelection').hasClass('notEmpty'))
			{
				$('#topMenu a.questionSelection').removeClass('notEmpty');
			}
	
		});
	},
	
	'_addTogglerListener': function(){
		$('ul.more').live('click',function(){
			
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
		$('td.question a').click(function(event){
			event.stopPropagation();
		});
	}

};