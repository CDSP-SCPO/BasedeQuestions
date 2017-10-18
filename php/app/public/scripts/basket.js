/**
 * 
 * A very simple client side Basket.
 * Relies on the given xHTML markup.
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet CDSP
 * @requires jQuery
 * @requires jQuery cookie
 */
var basket = {
	/**
	 * @var Object
	 */
	'cookie': null,
	/**
	 * @var Array
	 */
	'variables': null,
	/**
	 * @var Object
	 */
	'translate': null,
	/**
	 * @constructor
	 * @param {Object} params
	 * @type Object
	 */
	'init': function(params)
	{
		this.cookie = $.cookie('selectedQuestions');
		this.variables = this.cookie ? this.cookie.split(/,/) : [];
		this.translate = params.translate;
		this._addListeners();
		return this;
	},
	/**
	 * @param {int} val The variable id to add
	 */
	'add': function(val) {
		this.variables.push(val);
		$.cookie('selectedQuestions', this.variables, {expires: 10000, path: '/' });
	},
	/**
	 * Deletes everything from the basket
	 */
	'clear': function() {
		$.cookie('selectedQuestions', null, {expires: 10000, path: '/' });
	},
	/**
	 * Returns all variable ids in the basket
	 * @type array
	 */
	'items': function() {
		return this.variables;
	},
	/**
	 * Remove a variable from the basket
	 * @param {int} val The variable id to delete
	 */
	'remove': function(val) {
		this.variables.splice($.inArray(val, this.variables), 1);
		$.cookie('selectedQuestions', this.variables, {expires: 10000, path: '/' });
	},
	/**
	 * Returns the number of variables in the basket
	 * @type int
	 */
	'length': function() {
		return this.variables.length;
	},

	'_addListeners': function(){
		var count = $('#topMenu span.count');
		$('span.selectQuestion').live('click', function(){
			basket.add($(this).attr('id').split('_')[1]);
			$(this).attr("title", basket.translate.remove);
			$(this).removeClass('selectQuestion');
			$(this).addClass('removeQuestion');
			count.text(parseInt(count.text(), 10) + 1);

			if (! ($('#topMenu a.questionSelection').hasClass('notEmpty')))
			{
				$('#topMenu a.questionSelection').addClass('notEmpty');
			}

		});
		$('span.removeQuestion').live('click', function(){
			basket.remove($(this).attr('id').split('_')[1]);
			$(this).attr("title", basket.translate.add);
			$(this).removeClass('removeQuestion');
			$(this).addClass('selectQuestion');
			var c = parseInt(count.text(), 10) - 1;
			count.text(c);

			if (c === 0 && $('#topMenu a.questionSelection').hasClass('notEmpty'))
			{
				$('#topMenu a.questionSelection').removeClass('notEmpty');
			}

		});
	}

};