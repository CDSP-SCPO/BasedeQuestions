/**
 * 
 * Makes the columns resizable.
 * Relies on the given xHTML markup.
 * 
 * @author Xavier Schepler
 * @copyright Réseau Quetelet CDSP
 * @requires jQuery
 * @requires jQuery UI draggable
 * @requires jQuery cookie
 */
var BDQGrips = {

	/**
	 * The current center column left side position
	 * @var float
	 */
	'x':null,

	/**
	 * The left column / body ratio.
	 * @var float
	 */
	'k':null,

	/**
	 * The left draggable div wrapped in a jQuery object.
	 * @var Object
	 */
	'westGrip':null,

	/**
	 * The right draggable div wrapped in a jQuery object.
	 * @var Object
	 */
	'eastGrip':null,

	/**
	 * The center draggable div wrapped in a jQuery object.
	 * @var Object
	 */
	'innerGrip':null,

	/**
	 * Single column layout ?
	 * @var boolean
	 */
	'showLeftColumn':null,

	/**
	 * The left column div wrapped in a jQuery object.
	 * @var Object
	 */
	'leftColumn':null,

	/**
	 * The right column div wrapped in a jQuery object.
	 * @var Object
	 */
	'contentColumn':null,

	/**
	 * The main container div wrapped in a jQuery object.
	 * @var Object
	 */
	'mainContainer':null,

	/**
	 * The body wrapped in a jQuery object.
	 * @var Object
	 */
	'body':null,

	/**
	 * @var int
	 */
	'layoutMinWidth':980,

	/**
	 * @var int
	 */
	'outterGripHeightDelta':12,

	/**
	 * @var int
	 */
	'innerGripHeightDelta':5,

	/**
	 * @var int
	 */
	'gripWidth':7,

	/**
	 * @var int
	 */
	'cookieDuration':10000,
	
	/**
	 * @var boolean
	 */
	'dragDisabled': false,

	/**
	 * @constructor
	 * @type Object
	 */
	'init': function(){
		this.show();
		this.westGrip.draggable(
			{
				axis: 'x',
				containment:[0, 0, ($('html').width() - this.layoutMinWidth)/2, this.body.height()],
				cursor: 'col-resize',
				drag: function()
				{
					BDQGrips.westGripDrag();
					BDQGrips.resize();
				},
				start: function()
				{
					BDQGrips.westGripStart();
					BDQGrips.resizeStart();
				},
				stop: function()
				{
					BDQGrips.westGripStop();
					BDQGrips.resizeStop();
				}
			}
		);
		this.eastGrip.draggable(
			{
				axis: 'x',
				containment:[($('html').width() - this.layoutMinWidth)/2 + this.layoutMinWidth - this.gripWidth, 0, $('html').width() - this.gripWidth - 1, this.body.height()],
				cursor: 'col-resize',
				drag: function()
				{
					BDQGrips.eastGripDrag();
					BDQGrips.resize();
				},
				start: function()
				{
					BDQGrips.eastGripStart();
					BDQGrips.resizeStart();
				},
				stop: function()
				{
					BDQGrips.eastGripStop();
					BDQGrips.resizeStop();
				}
			}
		);

		if ($.browser.msie)
		{
			this.innerGrip.css('top', $('#contentWrapper').offset().top);
		}

		if (this.showLeftColumn)
		{
			this.innerGrip.draggable(
				{
					axis: 'x',
					containment: [this.westGrip.offset().left + this.gripWidth, 0, this.eastGrip.offset().left - this.gripWidth, 0],
					cursor: 'col-resize',
					drag: function()
					{
						BDQGrips.innerGripDrag();
						BDQGrips.resize();
					},
					start: function()
					{
						BDQGrips.innerGripStart();
						BDQGrips.resizeStart();
					},
					stop: function()
					{
						BDQGrips.innerGripStop();
						BDQGrips.resizeStop();
					}
				}
			);
		}

		return this;

	},
	
	'show': function(){
		this.leftColumn = $('#leftColumn');
		this.contentColumn = $('#contentColumn');
		this.mainContainer = $('#mainContainer');
		this.showLeftColumn =  ! this.mainContainer.hasClass('oneColumn');
		this.body = $('body');
		this.k = this.leftColumn.width() / this.body.width();
		this.body.append('<div class="grip" id="westGrip"></div>')
				.append('<div class="grip" id="eastGrip"></div>')
				.append('<div class="grip" id="innerGrip"></div>');
		this.westGrip = $('#westGrip');
		this.eastGrip = $('#eastGrip');
		this.innerGrip = $('#innerGrip');
		this.westGrip.css('top',0)
					.css('left', this.mainContainer.offset().left)
					.css('zIndex', 100)
					.height($(document).height());
		this.eastGrip.css('top',0)
					.css('left', this.westGrip.offset().left + this.mainContainer.width() - this.gripWidth)
					.css('zIndex', 101)
					.height($(document).height());
		this.innerGrip.css('left', this.contentColumn.offset().left)
					.height($(document).height() - $('#topSection').height() - this.outterGripHeightDelta)
					.css('zIndex', 99)
					.css('backgroundPosition', "center " + (300 - this.innerGrip.offset().top) + "px");

		if ( ! this.showLeftColumn)
		{
			this.innerGrip.css('opacity', 0)
							.css('cursor', 'auto');
			this.contentColumn.css('marginLeft', 0);
		};
	},
	
	'disable': function(){
		this.westGrip.addClass('disabled');
		this.eastGrip.addClass('disabled');
		this.innerGrip.addClass('disabled');
		this.dragDisabled = true;
	},

	/**
	 * Resize the layout left column
	 */
	'resizeLeftColumn': function(){
		var x = parseInt(this.k * this.body.width(), 10);
		this.leftColumn.width(x);
	
		if (this.showLeftColumn)
		{
			this.contentColumn.css('marginLeft', x);
		}

	},

	/**
	 * Resize the grips
	 */
	'resizeGrips': function(){
		this.westGrip.css('left', this.mainContainer.offset().left)
					.height($(document).height());

		this.eastGrip.css('left', this.westGrip.offset().left + this.mainContainer.width() - this.gripWidth)
					.height($(document).height());

		this.innerGrip.css('left', this.contentColumn.offset().left)
						.height($(document).height() - $('#topSection').height() - this.outterGripHeightDelta);
		
		if ( ! this.dragDisabled)
		{
			this.westGrip.draggable('option', 'containment', [0, 0, ($('html').width() - this.layoutMinWidth)/2, this.body.height()]);
			this.eastGrip.draggable('option', 'containment', [($('html').width() - this.layoutMinWidth)/2 + this.layoutMinWidth, 0, $('html').width(), this.body.height()]);
			
			if (this.showLeftColumn)
			{
				this.innerGrip.draggable('option', 'containment', [this.westGrip.offset().left + this.gripWidth, 0, this.eastGrip.offset().left - this.gripWidth, 0]);
			}
			
		}
		
		if ($('#rightColumn'))
		{
			$('#rightColumn').height($(document).height() - $('#topSection').height() - this.gripWidth);
		}

		this.resizeStop();
	},

	/**
	 * Callback during left grip drag
	 */
	'westGripDrag': function(){
		var delta = this.x - this.westGrip.offset().left;
		this.eastGrip.css('left', this.mainContainer.offset().left + this.mainContainer.width() - this.gripWidth);
		this.innerGrip.css('left', this.westGrip.offset().left + this.k * this.body.width());
		this.x = this.westGrip.offset().left;
		this.body.width(this.body.width() + delta * 2);
		this.resizeLeftColumn();
	},

	/**
	 * Callback for left grip drag start
	 */
	'westGripStart': function(){
		this.x = this.westGrip.offset().left;
		this.k = this.leftColumn.width() / this.body.width();
		$('.autocomplete').autocomplete('close');
	},

	/**
	 * Callback for left grip drag stop
	 */
	'westGripStop': function(){
		this.westGrip.css('left', this.mainContainer.offset().left);
		this.eastGrip.css('left', this.mainContainer.offset().left + this.mainContainer.width() - this.gripWidth);
		this.resizeLeftColumn();
		this.innerGrip.css('left', this.leftColumn.offset().left + this.leftColumn.width());
		
		if (this.showLeftColumn)
		{
			this.innerGrip.draggable('option', 'containment', [this.westGrip.offset().left + this.gripWidth, 0, this.eastGrip.offset().left - this.gripWidth, 0]);
		}

		var x = this.eastGrip.offset().left - this.westGrip.offset().left;
		$.cookie("settings[bodySize]", (x + this.gripWidth) + "px", { expires: this.cookieDuration, path: '/' });
		x = parseInt(this.innerGrip.offset().left - this.westGrip.offset().left, 10);
		$.cookie("settings[refineMenuSize]", x + "px", { expires: this.cookieDuration, path: '/' });
		this.eastGrip.css('height', $(document).height());
		this.westGrip.css('height', $(document).height());
		this.innerGrip.css('height', $(document).height() - $('#topSection').height() - this.innerGripHeightDelta);
	},

	/**
	 * Callback during inner grip drag
	 */
	'innerGripDrag': function(){
		var x = parseInt(this.innerGrip.offset().left - this.westGrip.offset().left, 10);
		this.leftColumn.width(x);
		this.contentColumn.css('marginLeft', x); 
	},

	/**
	 * Callback for inner grip drag start
	 */
	'innerGripStart': function(){
		$('.autocomplete').autocomplete('close');
	},

	/**
	 * Callback for inner grip drag stop
	 */
	'innerGripStop': function(){
		this.innerGrip.css('left', this.contentColumn.offset().left);
		var x = parseInt(this.innerGrip.offset().left - this.westGrip.offset().left, 10);
		$.cookie("settings[refineMenuSize]", x + "px", { expires: this.cookieDuration, path: '/' });
		this.eastGrip.css('height', $(document).height());
		this.westGrip.css('height', $(document).height());
		this.innerGrip.css('height', $(document).height() - $('#topSection').height() - this.innerGripHeightDelta);
	},

	/**
	 * Callback during right grip drag
	 */
	'eastGripDrag': function(){
		var delta = this.eastGrip.offset().left - this.x;
		this.x = this.eastGrip.offset().left;
		this.body.width(this.body.width() + delta * 2);
		this.westGrip.css('left', this.mainContainer.offset().left);
		this.innerGrip.css('left', this.westGrip.offset().left + this.k * this.body.width());
		this.resizeLeftColumn();
	},

	/**
	 * Callback for right grip drag start
	 */
	'eastGripStart': function(){
		this.x = this.eastGrip.offset().left;
		this.k = this.leftColumn.width() / this.body.width();
		$('.autocomplete').autocomplete('close');
	},

	/**
	 * Callback for right grip drag stop
	 */
	'eastGripStop': function(){
		this.westGrip.css('left', this.mainContainer.offset().left);
		this.eastGrip.css('left', this.westGrip.offset().left + this.mainContainer.width() - this.gripWidth);
		this.resizeLeftColumn();
		this.innerGrip.css('left', this.leftColumn.offset().left + this.leftColumn.width());

		if (this.showLeftColumn)
		{
			this.innerGrip.draggable('option', 'containment', [this.westGrip.offset().left + this.gripWidth, 0, this.eastGrip.offset().left - this.gripWidth, 0]);
		}

		var x = this.eastGrip.offset().left - this.westGrip.offset().left;
		$.cookie("settings[bodySize]", (x + this.gripWidth) + "px", { expires: this.cookieDuration, path: '/' });
		x = parseInt(this.innerGrip.offset().left - this.westGrip.offset().left, 10);
		$.cookie("settings[refineMenuSize]", x + "px", { expires: this.cookieDuration, path: '/' });
		this.eastGrip.css('height', $(document).height());
		this.westGrip.css('height', $(document).height());
		this.innerGrip.css('height', $(document).height() - $('#topSection').height() - this.innerGripHeightDelta);
	},
	
	/**
	 * User defined callback
	 */
	'resizeStop': function(){
	},
	
	/**
	 * User defined callback
	 */
	'resizeStart': function(){
	},

	/**
	 * User defined callback
	 */
	'resize': function(){
	}

};

$(document).ready(function(){
	$(window).resize(function(){
		BDQGrips.resizeGrips();
	});
});