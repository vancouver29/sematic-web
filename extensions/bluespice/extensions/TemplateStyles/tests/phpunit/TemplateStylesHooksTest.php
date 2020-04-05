<?php

/**
 * @group TemplateStyles
 * @group Database
 * @covers TemplateStylesHooks
 */
class TemplateStylesHooksTest extends MediaWikiLangTestCase {

	protected function addPage( $page, $text, $model ) {
		$title = Title::newFromText( 'Template:TemplateStyles test/' . $page );
		$content = ContentHandler::makeContent( $text, $title, $model );

		$page = WikiPage::factory( $title );
		$user = static::getTestSysop()->getUser();
		$status = $page->doEditContent( $content, 'Test for TemplateStyles', 0, false, $user );
		if ( !$status->isOk() ) {
			$this->fail( "Failed to create $title: " . $status->getWikiText( false, false, 'en' ) );
		}
	}

	public function addDBDataOnce() {
		$this->addPage( 'wikitext', '.foo { color: red; }', CONTENT_MODEL_WIKITEXT );
		$this->addPage( 'nonsanitized.css', '.foo { color: red; }', CONTENT_MODEL_CSS );
		$this->addPage( 'styles1.css', '.foo { color: blue; }', 'sanitized-css' );
		$this->addPage( 'styles2.css', '.bar { color: green; }', 'sanitized-css' );
	}

	/**
	 * @dataProvider provideOnRegistration
	 * @param array $textModelsToParse
	 * @param bool $autoParseContent
	 * @param array $expect
	 */
	public function testOnRegistration( $textModelsToParse, $autoParseContent, $expect ) {
		$this->setMwGlobals( [
			'wgTextModelsToParse' => $textModelsToParse,
			'wgTemplateStylesAutoParseContent' => $autoParseContent,
		] );

		global $wgTextModelsToParse;
		TemplateStylesHooks::onRegistration();
		$this->assertSame( $expect, $wgTextModelsToParse );
	}

	public static function provideOnRegistration() {
		return [
			[
				[ CONTENT_MODEL_WIKITEXT ],
				true,
				[ CONTENT_MODEL_WIKITEXT ]
			],
			[
				[ CONTENT_MODEL_WIKITEXT, CONTENT_MODEL_CSS ],
				true,
				[ CONTENT_MODEL_WIKITEXT, CONTENT_MODEL_CSS, 'sanitized-css' ],
			],
			[
				[ CONTENT_MODEL_WIKITEXT, CONTENT_MODEL_CSS ],
				false,
				[ CONTENT_MODEL_WIKITEXT, CONTENT_MODEL_CSS ],
			],
		];
	}

	/**
	 * @dataProvider provideOnParserAfterTidy
	 */
	public function testOnParserAfterTidy( $text, $expect ) {
		$p = new Parser();
		TemplateStylesHooks::onParserAfterTidy( $p, $text );
		$this->assertSame( $expect, $text );
	}

	public static function provideOnParserAfterTidy() {
		return [
			[
				"<style>\n.foo { color: red; }\n</style>",
				"<style>\n.foo { color: red; }\n</style>",
			],
			[
				"<style>\n<![CDATA[\n.foo { color: red; }\n]]>\n</style>",
				"<style>\n/*<![CDATA[*/\n.foo { color: red; }\n/*]]>*/\n</style>",
			],
			[
				"<StYlE type='text/css'>\n<![CDATA[\n.foo { color: red; }\n]]>\n</sTyLe>",
				"<StYlE type='text/css'>\n/*<![CDATA[*/\n.foo { color: red; }\n/*]]>*/\n</sTyLe>",
			],
			[
				"<style>\n/*<![CDATA[*/\n.foo { color: red; }\n/*]]>*/\n</style>",
				"<style>\n/*<![CDATA[*/\n.foo { color: red; }\n/*]]>*/\n</style>",
			],
			[
				"<style>x\n<![CDATA[\n.foo { color: red; }\n]]>\n</style>",
				"<style>x\n<![CDATA[\n.foo { color: red; }\n/*]]>*/\n</style>",
			],
			[
				"<script>\n<![CDATA[\n.foo { color: red; }\n]]>\n</script>",
				"<script>\n<![CDATA[\n.foo { color: red; }\n]]>\n</script>",
			],
		];
	}

	/**
	 * @dataProvider provideOnContentHandlerDefaultModelFor
	 */
	public function testOnContentHandlerDefaultModelFor( $ns, $title, $expect ) {
		$this->setMwGlobals( [
			'wgTemplateStylesNamespaces' => [ 10 => true, 2 => false, 3000 => true, 3002 => true ],
			'wgNamespacesWithSubpages' => [ 10 => true, 2 => true, 3000 => true, 3002 => false ],
		] );

		$model = 'unchanged';
		$ret = TemplateStylesHooks::onContentHandlerDefaultModelFor(
			Title::makeTitle( $ns, $title ), $model
		);
		$this->assertSame( !$expect, $ret );
		$this->assertSame( $expect ? 'sanitized-css' : 'unchanged', $model );
	}

	public static function provideOnContentHandlerDefaultModelFor() {
		return [
			[ 10, 'Test/test.css', true ],
			[ 10, 'Test.css', false ],
			[ 10, 'Test/test.xss', false ],
			[ 10, 'Test/test.CSS', false ],
			[ 3000, 'Test/test.css', true ],
			[ 3002, 'Test/test.css', false ],
			[ 2, 'Test/test.css', false ],
		];
	}

	/**
	 * @dataProvider provideOnCodeEditorGetPageLanguage
	 */
	public function testOnCodeEditorGetPageLanguage( $useCodeEditor, $model, $expect ) {
		$this->setMwGlobals( [
			'wgTemplateStylesUseCodeEditor' => $useCodeEditor,
		] );

		$title = Title::makeTitle( NS_TEMPLATE, 'Test.css' );
		$lang = 'unchanged';
		$ret = TemplateStylesHooks::onCodeEditorGetPageLanguage(
			$title, $lang, $model, 'text/x-whatever'
		);
		$this->assertSame( !$expect, $ret );
		$this->assertSame( $expect ? 'css' : 'unchanged', $lang );
	}

	public static function provideOnCodeEditorGetPageLanguage() {
		return [
			[ true, 'wikitext', false ],
			[ true, 'css', false ],
			[ true, 'sanitized-css', true ],
			[ false, 'sanitized-css', false ],
		];
	}

	/**
	 * Unfortunately we can't just use a parserTests.txt file because our
	 * tag's output depends on the revision IDs of the input pages.
	 * @dataProvider provideTag
	 */
	public function testTag(
		ParserOptions $popt, $getTextOptions, $wikitext, $expect, $globals = []
	) {
		global $wgParserConf;

		$this->setMwGlobals( $globals + [
			'wgScriptPath' => '',
			'wgScript' => '/index.php',
			'wgArticlePath' => '/wiki/$1',
		] );

		$oldCurrentRevisionCallback = $popt->setCurrentRevisionCallback(
			function ( Title $title, $parser = false ) use ( &$oldCurrentRevisionCallback ) {
				if ( $title->getPrefixedText() === 'Template:Test replacement' ) {
					$user = RequestContext::getMain()->getUser();
					return new Revision( [
						'page' => $title->getArticleID(),
						'user_text' => $user->getName(),
						'user' => $user->getId(),
						'parent_id' => $title->getLatestRevID(),
						'title' => $title,
						'content' => new TemplateStylesContent( '.baz { color:orange; bogus:bogus; }' )
					] );
				}
				return call_user_func( $oldCurrentRevisionCallback, $title, $parser );
			}
		);

		$class = $wgParserConf['class'];
		$parser = new $class( $wgParserConf );
		/** @var Parser $parser */
		$parser->firstCallInit();
		if ( !isset( $parser->mTagHooks['templatestyles'] ) ) {
			throw new Exception( 'templatestyles tag hook is not in the parser' );
		}
		$out = $parser->parse( $wikitext, Title::newFromText( 'Test' ), $popt );
		$parser->mPreprocessor = null; # Break the Parser <-> Preprocessor cycle

		$expect = preg_replace_callback( '/\{\{REV:(.*?)\}\}/', function ( $m ) {
			return Title::newFromText( 'Template:TemplateStyles test/' . $m[1] )->getLatestRevID();
		}, $expect );
		$this->assertEquals( $expect, $out->getText( $getTextOptions ) );
	}

	public static function provideTag() {
		$popt = ParserOptions::newFromContext( RequestContext::getMain() );
		$popt->setWrapOutputClass( 'templatestyles-test' );

		$popt2 = ParserOptions::newFromContext( RequestContext::getMain() );

		$popt3 = ParserOptions::newFromContext( RequestContext::getMain() );
		Wikimedia\quietCall( [ $popt3, 'setWrapOutputClass' ], false );

		return [
			'Tag without src' => [
				$popt, [],
				'<templatestyles />',
				// @codingStandardsIgnoreStart Ignore Generic.Files.LineLength.TooLong
				"<div class=\"templatestyles-test\"><p><strong class=\"error\">TemplateStyles' <code>src</code> attribute must not be empty.</strong>\n</p></div>",
				// @codingStandardsIgnoreEnd
			],
			'Tag with invalid src' => [
				$popt, [],
				'<templatestyles src="Test&lt;&gt;" />',
				// @codingStandardsIgnoreStart Ignore Generic.Files.LineLength.TooLong
				"<div class=\"templatestyles-test\"><p><strong class=\"error\">Invalid title for TemplateStyles' <code>src</code> attribute.</strong>\n</p></div>",
				// @codingStandardsIgnoreEnd
			],
			'Tag with valid but nonexistent title' => [
				$popt, [],
				'<templatestyles src="ThisDoes\'\'\'Not\'\'\'Exist" />',
				// @codingStandardsIgnoreStart Ignore Generic.Files.LineLength.TooLong
				"<div class=\"templatestyles-test\"><p><strong class=\"error\">Page <a href=\"/index.php?title=Template:ThisDoes%27%27%27Not%27%27%27Exist&amp;action=edit&amp;redlink=1\" class=\"new\" title=\"Template:ThisDoes&#39;&#39;&#39;Not&#39;&#39;&#39;Exist (page does not exist)\">Template:ThisDoes&#39;&#39;&#39;Not&#39;&#39;&#39;Exist</a> has no content.</strong>\n</p></div>",
				// @codingStandardsIgnoreEnd
			],
			'Tag with valid but nonexistent title, main namespace' => [
				$popt, [],
				'<templatestyles src=":ThisDoes\'\'\'Not\'\'\'Exist" />',
				// @codingStandardsIgnoreStart Ignore Generic.Files.LineLength.TooLong
				"<div class=\"templatestyles-test\"><p><strong class=\"error\">Page <a href=\"/index.php?title=ThisDoes%27%27%27Not%27%27%27Exist&amp;action=edit&amp;redlink=1\" class=\"new\" title=\"ThisDoes&#39;&#39;&#39;Not&#39;&#39;&#39;Exist (page does not exist)\">ThisDoes&#39;&#39;&#39;Not&#39;&#39;&#39;Exist</a> has no content.</strong>\n</p></div>",
				// @codingStandardsIgnoreEnd
			],
			'Tag with wikitext page' => [
				$popt, [],
				'<templatestyles src="TemplateStyles test/wikitext" />',
				// @codingStandardsIgnoreStart Ignore Generic.Files.LineLength.TooLong
				"<div class=\"templatestyles-test\"><p><strong class=\"error\">Page <a href=\"/wiki/Template:TemplateStyles_test/wikitext\" title=\"Template:TemplateStyles test/wikitext\">Template:TemplateStyles test/wikitext</a> must have content model \"Sanitized CSS\" for TemplateStyles (current model is \"wikitext\").</strong>\n</p></div>",
				// @codingStandardsIgnoreEnd
			],
			'Tag with CSS (not sanitized-css) page' => [
				$popt, [],
				'<templatestyles src="TemplateStyles test/nonsanitized.css" />',
				// @codingStandardsIgnoreStart Ignore Generic.Files.LineLength.TooLong
				"<div class=\"templatestyles-test\"><p><strong class=\"error\">Page <a href=\"/wiki/Template:TemplateStyles_test/nonsanitized.css\" title=\"Template:TemplateStyles test/nonsanitized.css\">Template:TemplateStyles test/nonsanitized.css</a> must have content model \"Sanitized CSS\" for TemplateStyles (current model is \"CSS\").</strong>\n</p></div>",
				// @codingStandardsIgnoreEnd
			],
			'Working tag' => [
				$popt, [],
				'<templatestyles src="TemplateStyles test/styles1.css" />',
				// @codingStandardsIgnoreStart Ignore Generic.Files.LineLength.TooLong
				"<div class=\"templatestyles-test\"><style data-mw-deduplicate=\"TemplateStyles:r{{REV:styles1.css}}/templatestyles-test\">.templatestyles-test .foo{color:blue}</style>\n</div>",
				// @codingStandardsIgnoreEnd
			],
			'Disabled' => [
				$popt, [],
				'<templatestyles src="TemplateStyles test/styles1.css" />',
				"<div class=\"templatestyles-test\"></div>",
				[ 'wgTemplateStylesDisable' => true ],
			],
			'Replaced content (which includes sanitization errors)' => [
				$popt, [],
				'<templatestyles src="Test replacement" />',
				// @codingStandardsIgnoreStart Ignore Generic.Files.LineLength.TooLong
				"<div class=\"templatestyles-test\"><style data-mw-deduplicate=\"TemplateStyles:8fd14043c1cce91e8b9d1487a9d17d8d9ae43890/templatestyles-test\">/*\nErrors processing stylesheet [[:Template:Test replacement]] (rev ):\n• Unrecognized or unsupported property at line 1 character 22.\n*/\n.templatestyles-test .baz{color:orange}</style>\n</div>",
				// @codingStandardsIgnoreEnd
			],
			'Still prefixed despite no wrapper' => [
				$popt2, [ 'unwrap' => true ],
				'<templatestyles src="TemplateStyles test/styles1.css" />',
				// @codingStandardsIgnoreStart Ignore Generic.Files.LineLength.TooLong
				"<style data-mw-deduplicate=\"TemplateStyles:r{{REV:styles1.css}}\">.mw-parser-output .foo{color:blue}</style>\n",
				// @codingStandardsIgnoreEnd
			],
			'Still prefixed despite deprecated no wrapper' => [
				$popt3, [],
				'<templatestyles src="TemplateStyles test/styles1.css" />',
				// @codingStandardsIgnoreStart Ignore Generic.Files.LineLength.TooLong
				"<style data-mw-deduplicate=\"TemplateStyles:r{{REV:styles1.css}}\">.mw-parser-output .foo{color:blue}</style>\n",
				// @codingStandardsIgnoreEnd
			],
			'Deduplicated tags' => [
				$popt, [],
				trim( '
<templatestyles src="TemplateStyles test/styles1.css" />
<templatestyles src="TemplateStyles test/styles1.css" />
<templatestyles src="TemplateStyles test/styles2.css" />
<templatestyles src="TemplateStyles test/styles1.css" />
<templatestyles src="TemplateStyles test/styles2.css" />
				' ),
				// @codingStandardsIgnoreStart Ignore Generic.Files.LineLength.TooLong
				trim( '
<div class="templatestyles-test"><style data-mw-deduplicate="TemplateStyles:r{{REV:styles1.css}}/templatestyles-test">.templatestyles-test .foo{color:blue}</style>
<link rel="mw-deduplicated-inline-style" href="mw-data:TemplateStyles:r{{REV:styles1.css}}/templatestyles-test"/>
<style data-mw-deduplicate="TemplateStyles:r{{REV:styles2.css}}/templatestyles-test">.templatestyles-test .bar{color:green}</style>
<link rel="mw-deduplicated-inline-style" href="mw-data:TemplateStyles:r{{REV:styles1.css}}/templatestyles-test"/>
<link rel="mw-deduplicated-inline-style" href="mw-data:TemplateStyles:r{{REV:styles2.css}}/templatestyles-test"/>
</div>
				' ),
				// @codingStandardsIgnoreEnd
			],
		];
	}

}
