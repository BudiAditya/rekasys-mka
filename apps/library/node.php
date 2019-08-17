<?php
/**
 * Created by JetBrains PhpStorm.
 * User: it-dev
 * Date: 4/5/12
 * Time: 9:33 AM
 * To change this template use File | Settings | File Templates.
 */
class Node {
	public $Text;
	public $Url;
	public $Nodes = array();
	public $Visible = false;
	/**
	 * @var Node
	 */
	public $Parent = null;
	public $Type = null;
	/** @var null|Node */
	public $TitleNode = null;
	/**
	 * Berfungsi untuk menyimpan data Node dengan type 'title' yang parnah ditemukan pada proses sebelumnya
	 * Title mirip sub menu tetapi kita tidak punya direct link nya dari AddNode() karena ini akan sejajar menu/submenu
	 * Trick: Pake static field untuk simpan referensinya
	 *
	 * @var null|Node
	 */
	private static $_PrevTitleNode = null;

	public function __construct($name, $url = null, Node $parent = null, $type = "link") {
		$this->Text = $name;
		$this->Url = $url;
		$this->Parent = $parent;
		$this->Type = $type;

		// Special case
		if ($name == "-") {
			// Separator ini...
			$this->Visible = true;
			$this->Url = $url = null;
		}

		if ($type == "title") {
			Node::$_PrevTitleNode = $this;
		}

		if ($url == null) {
			$this->Visible = false;
			return;
		}

		// Kita coba cek hak aksesnya disini
		// URL possibility: [namespace.]controller[/method[/param]]
		$tokens = explode("/", $url, 3);
		$namespace = null;
		$controller = $tokens[0];
		$method = isset($tokens[1]) ? $tokens[1] : DEFAULT_METHOD;
		if (strpos($controller, ".") !== false) {
			$tokens = explode(".", $controller);
			$total = count($tokens);
			$controller = $tokens[$total - 1];
			unset($tokens[$total - 1]);
			$namespace = implode(".", $tokens);
		}

		$acl = AclManager::GetInstance();
		$this->Visible = $acl->CheckUserAccess($controller, $method, $namespace);
		$this->TitleNode = Node::$_PrevTitleNode;

		if ($this->Visible) {
			// Kalau salah satu anaknya ada yang visible maka parentnya wajib visible agar menu anaknya juga visible
			$this->MakeSureParentVisible();
		}
	}

	public function AddNode($name, $url = null, $type = "link") {
		$node = new Node($name, $url, $this, $type);
		$this->Nodes[] = $node;
		return $node;
	}

	public function AddSeparator() {
		$node = new Node(null, null, $this, "sep");
		$this->Nodes[] = $node;
		return $node;
	}

	public function MakeSureParentVisible() {
		if ($this->TitleNode != null) {
			$this->TitleNode->Visible = true;
		}
		if ($this->Parent == null) {
			// No parent to be processed...
			return;
		}
		$this->Parent->Visible = true;
		$this->Parent->MakeSureParentVisible();
	}
}

// End of file: node.php
