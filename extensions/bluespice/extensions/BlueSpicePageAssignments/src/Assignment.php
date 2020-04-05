<?php
namespace BlueSpice\PageAssignments;

use MediaWiki\Linker\LinkRenderer;
use BlueSpice\PageAssignments\Data\Record;

abstract class Assignment implements IAssignment, \JsonSerializable {

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var string
	 */
	protected $title = null;

	/**
	 *
	 * @var string
	 */
	protected $type = null;

	/**
	 *
	 * @var string
	 */
	protected $key = null;

	/**
	 *
	 * @var LinkRenderer
	 */
	protected $linkRenderer = null;

	/**
	 *
	 * @var HTML, rendered anchor tag for this assignment
	 */
	protected $anchor = null;

	/**
	 *
	 * @param \Config $config
	 * @param LinkRenderer $linkRenderer
	 * @param \Title $title
	 * @param string $type
	 * @param string $key
	 */
	public function __construct( \Config $config, LinkRenderer $linkRenderer, \Title $title, $type, $key ) {
		$this->config = $config;
		$this->title = $title;
		$this->linkRenderer = $linkRenderer;
		$this->key = $key;
		$this->type = $type;
	}

	public function jsonSerialize() {
		return $this->getRecord()->jsonSerialize();
	}

	//Needed for ExtJSStoreBase implementation
	public function toStdClass() {
		return (object) $this->jsonSerialize();
	}

	/**
	 * @return string
	 */
	abstract protected function makeAnchor();

	public function getType() {
		return $this->type;
	}

	public function getKey() {
		return $this->key;
	}

	public function getAnchor() {
		if( $this->anchor ) {
			return $this->anchor;
		}
		$this->anchor = $this->makeAnchor();
		return $this->anchor;
	}

	public function getRecord() {
		return new Record( (object)[
			Record::TEXT => $this->getText(),
			Record::ASSIGNEE_KEY => $this->getKey(),
			Record::ASSIGNEE_TYPE => $this->getType(),
			Record::ID => $this->getId(),
			Record::POSITION => $this->getPosition(),
			Record::ANCHOR => $this->getAnchor(),
			Record::PAGE_ID => $this->getTitle()->getArticleID()
		]);
	}

	/**
	 *
	 * @return \Title
	 */
	public function getTitle() {
		return $this->title;
	}

	public function getPosition() {
		return 0;
	}

	public function getId() {
		return "{$this->getType()}/{$this->getKey()}";
	}
}
