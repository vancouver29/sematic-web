/**
 * InsertLink js
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceInsertLink
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

//PW(28.09.2013) TODO: use FormPanelFileLink context
onFileDialogFile = function(path) {
	Ext.getCmp('BSInserLinkTargetUrl').setValue(path);
};
//PW(28.09.2013) TODO: use FormPanelFileLink context
onFileDialogCancel = function() {
};

$(document).on('click', '#bs-editbutton-insertlink', function() {
	var me = this;
	mw.loader.using( 'ext.bluespice.extjs' ).done(function(){
		Ext.require('BS.InsertLink.Window', function() {
			BS.InsertLink.Window.resetData();
			BS.InsertLink.Window.clearListeners();
			BS.InsertLink.Window.on('ok', BsInsertLinkWikiTextConnector.applyData, this);
			BS.InsertLink.Window.on('cancel', bs.util.selection.reset);
			BsInsertLinkWikiTextConnector.getData();
			BS.InsertLink.Window.setData(
				BsInsertLinkWikiTextConnector.getData()
			);
			BS.InsertLink.Window.show(me);
		});
	});
});

$(document).bind('BsVisualEditorActionsInit', function(event, plugin, buttons, commands, menus) {
	buttons.push({
		buttonId: 'bslink',
		buttonConfig: {
			icon: 'link',
			title: mw.message('bs-insertlink-button-title').plain(),
			cmd: 'mceBsLink'
		}
	});

	//We use the standard TinyMCE functionality for this!
	buttons.push({
		buttonId: 'unlink',
		buttonConfig: {
			icon: 'unlink',
			tooltip: 'Remove link',
			cmd: 'unlink',
			stateSelector: 'a[href]'
		}
	});

	menus.push({
		menuId: 'bsContextLink',
		menuConfig: {
			text: mw.message('bs-insertlink-button-title').plain(),
			icon: 'link',
			cmd: 'mceBsLink'
		}
	});

	menus.push({
		menuId: 'bsContextUnlink',
		menuConfig: {
			icon: 'unlink',
			text: 'Remove link',
			cmd: 'unlink',
			onPostRender: function() {
				var ctrl = this;
				tinyMCE.activeEditor.on('NodeChange', function(e) {
					ctrl.disabled(e.element.nodeName != 'A');
					ctrl.visible(e.element.nodeName == 'A');
				});
			}
		}
	});

	commands.push({
		commandId: 'mceBsLink',
		commandCallback: function() {
			var editor = tinyMCE.activeEditor;
			var me = this;
			mw.loader.using( 'ext.bluespice.extjs' ).done(function(){
				Ext.require('BS.InsertLink.Window', function() {
					BS.InsertLink.Window.clearListeners();
					BS.InsertLink.Window.on('ok', BsInsertLinkVisualEditorConnector.applyData, this, plugin, editor);
					BS.InsertLink.Window.resetData();
					BS.InsertLink.Window.setData(
							BsInsertLinkVisualEditorConnector.getData(plugin, editor)
							);
					BS.InsertLink.Window.show('bslink');
				}, me);
			});
		}
	});
});

var BsInsertLinkWikiTextConnector = {
	getData: function() {
		return {code: bs.util.selection.save()};
	},
	applyData: function(window, data) {
		if( data === null ) return;

		if (data.href === "" || data.href === "mailto:" || data.href === "href://") {
			bs.util.alert('bs-insertLink-empty-field',
					{
						text: mw.message( 'bs-insertlink-empty-field-text' ).plain()
					}, {
				ok: function() {
					BS.InsertLink.Window.show('bslink');
				},
				cancel: function() {
				},
				scope: this
			}
			);
			return;
		}
		bs.util.selection.restore(data.code);
	}
};

var BsInsertLinkVisualEditorConnector = {
	getData: function(plugin, editor) {
		var data = {};
		var node = editor.selection.getNode();

		BsInsertLinkVisualEditorConnector.bookmark = editor.selection.getBookmark();
		var link = editor.dom.getParent(node, "a");

		if (!link && node) {
			// Maybe link is already included in selection
			var nodeName = node.nodeName.toLowerCase();
			if (nodeName == 'a')
				link = node;
		}
		if (link) {
			editor.selection.select(link);

			data.href = decodeURIComponent(editor.dom.getAttrib(link, "href"));
			data.raw = editor.dom.getOuterHTML(link);
			data.type = editor.dom.getAttrib(link, "data-bs-type");
			// This is a jquery workaround to strip the tags from link.innerHTML
			data.content = '';
			for( var i = 0; i < link.childNodes.length; i++ ) {
				if( link.childNodes[i].nodeType === document.TEXT_NODE ) {
					data.content += link.childNodes[i].nodeValue;
				}
			}
			data.link = link;
		}
		else {
			var hwcontent = editor.selection.getContent();
			data.content = hwcontent.replace(/<.*?>/ig, '');
		}
		//data.selection = editor.selection.getBookmark();

		// Fix bug with cursor after table. IE will place everything within the table otherwise.
		// Solution: move selectionstart one step further
		var parentTag = editor.dom.getParent(node);
		if (parentTag.nodeName.toLowerCase() == 'body') {
			data.selection.start++;
		}

		return data;
	},
	applyData: function(window, data, plugin) {
		if( data === null ) return;

		var editor = plugin.getEditor();
		editor.focus();
		editor.selection.moveToBookmark(BsInsertLinkVisualEditorConnector.bookmark);
		var node = editor.selection.getNode();
		var newAnchor = null;

		var code = data.code;
		//Trim left and right everything (including linebreaks) that is not a starting or ending link code
		//This is necessary to avoid the bswikicode parser from breakin the markup
		code = code.replace(/(^.*?\[|\].*?$|\r\n|\r|\n)/gm, ''); //first layer of '[...]' //external-, file- and mailto- links
		code = code.replace(/(^.*?\[|\].*?$|\r\n|\r|\n)/gm, ''); //potential second layer of '[[...]]' //internal and interwiki links

		if (node.nodeName.toLowerCase() === 'a') {
			newAnchor = editor.dom.create(
				'a',
				{
					'title': data.title ? data.title : data.href,
					'href': "bs://" + data.href,
					'data-mce-href': data.href,
					//'class': data.class,
					'data-bs-type': data.type,
					'data-bs-wikitext': code
				},
				data.title ? data.title : data.href
			);
			editor.dom.replace(newAnchor, node);
			editor.selection.select(newAnchor, false);
			editor.selection.collapse(false);

			return;
		}

		newAnchor = editor.dom.createHTML(
			'a',
			{
				'title': data.title ? data.title : data.href,
				'href': "bs://" + data.href,
				'data-mce-href': data.href,
				//'class': data.class,
				'data-bs-type': data.type,
				'data-bs-wikitext': code
			},
			data.title ? data.title : data.href
		);

		// The following code used to be a workaround for IE. Other browsers used
		// these lines:
		//   editor.insertContent(newAnchor);
		//   editor.selection.collapse(false);
		// However, the non-workaround way lead to removal of spaces after (Chrome)
		// and before (FF) the link text. The IE code works in all browsers.
		var sel = editor.selection.getSel().toString();
		if( sel ) {
			var content = node.innerHTML;
			content = content.replace(sel, newAnchor);

			var newNode = editor.dom.create( node.nodeName.toLowerCase(), {}, content );

			editor.dom.replace(newNode, node);

			return;
		} else {
			editor.insertContent(newAnchor);
		}
	}
};

Ext.onReady( function() {
	Ext.Loader.setPath(
		"BS.InsertLink",
		bs.em.paths.get( 'BlueSpiceInsertLink' ) + '/resources/BS.InsertLink'
	);
} );
