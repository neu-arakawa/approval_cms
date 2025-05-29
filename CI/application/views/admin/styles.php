<section class="content">

  <section class="content-header">
    <div class="row">
        <div class="col-lg-7">
            <h2><i class="fas fa-gavel"></i> スタイル調整用画面</h2>
        </div>
    </div>
  </section>

  <?php if (ENVIRONMENT==='local' || ENVIRONMENT==='neulocal'):?>

  <div class="alert alert-warning" role="alert">
    この画面は管理画面のスタイル調整用の部品リストを掲載しています。<br>
    ローカル環境以外では非表示になります。
  </div>

  <div class="list-box">

  <style type="text/css">
  h4{
  	font-size: 1rem;
  	margin-top: 1rem;
  }
  .popover {
      position: relative;
      display: block;
      float: left;
      width: 260px;
      margin: 1.25rem;
  }
  .tooltip{
  	    position: relative;
      display: inline-block;
      margin: 10px 20px;
      opacity: 1;
  }
  </style>

  <h4>テキスト</h4>
  <span class="text-primary">.text-primary</span>
  <span class="text-secondary">.text-secondary</span>
  <span class="text-success">.text-success</span>
  <span class="text-danger">.text-danger</span>
  <span class="text-warning">.text-warning</span>
  <span class="text-info">.text-info</span>
  <span class="text-light bg-dark">.text-light</span>
  <span class="text-dark">.text-dark</span>
  <span class="text-muted">.text-muted</span>
  <span class="text-white bg-dark">.text-white</span>
  <hr>
  <span><a href="#" class="text-primary">Primary link</a></span>
  <span><a href="#" class="text-secondary">Secondary link</a></span>
  <span><a href="#" class="text-success">Success link</a></span>
  <span><a href="#" class="text-danger">Danger link</a></span>
  <span><a href="#" class="text-warning">Warning link</a></span>
  <span><a href="#" class="text-info">Info link</a></span>
  <span><a href="#" class="text-light bg-dark">Light link</a></span>
  <span><a href="#" class="text-dark">Dark link</a></span>
  <span><a href="#" class="text-muted">Muted link</a></span>
  <span><a href="#" class="text-white bg-dark">White link</a></span>

  <h4>テキストサイズ</h4>
  <span class="text-xs">かなり小さいサイズのテキスト</span>
  <span class="text-sm">小さいサイズのテキスト</span>
  <span class="">通常サイズのテキスト</span>
  <span class="text-lg">大きいサイズのテキスト</span>

  <h4>背景色</h4>
  <div class="container-fluid">
  <div class="row">
  	<div class="col-2 p-3 mb-2 bg-primary text-white">.bg-primary</div>
  	<div class="col-2 p-3 mb-2 bg-secondary text-white">.bg-secondary</div>
  	<div class="col-2 p-3 mb-2 bg-success text-white">.bg-success</div>
  	<div class="col-2 p-3 mb-2 bg-danger text-white">.bg-danger</div>
  	<div class="col-2 p-3 mb-2 bg-warning text-white">.bg-warning</div>
  	<div class="col-2 p-3 mb-2 bg-info text-white">.bg-info</div>
  	<div class="col-2 p-3 mb-2 bg-light text-dark">.bg-light</div>
  	<div class="col-2 p-3 mb-2 bg-dark text-white">.bg-dark</div>
  	<div class="col-2 p-3 mb-2 bg-white text-dark">.bg-white</div>
  </div>
  </div>

  <h4>バッジ</h4>
  <span class="badge badge-primary">Primary</span>
  <span class="badge badge-secondary">Secondary</span>
  <span class="badge badge-success">Success</span>
  <span class="badge badge-danger">Danger</span>
  <span class="badge badge-warning">Warning</span>
  <span class="badge badge-info">Info</span>
  <span class="badge badge-light">Light</span>
  <span class="badge badge-dark">Dark</span>

  <h4>バッジ（角丸）</h4>
  <span class="badge badge-pill badge-primary">Primary</span>
  <span class="badge badge-pill badge-secondary">Secondary</span>
  <span class="badge badge-pill badge-success">Success</span>
  <span class="badge badge-pill badge-danger">Danger</span>
  <span class="badge badge-pill badge-warning">Warning</span>
  <span class="badge badge-pill badge-info">Info</span>
  <span class="badge badge-pill badge-light">Light</span>
  <span class="badge badge-pill badge-dark">Dark</span>

  <h4>バッジ（リンク）</h4>
  <a href="#" class="badge badge-primary">Primary</a>
  <a href="#" class="badge badge-secondary">Secondary</a>
  <a href="#" class="badge badge-success">Success</a>
  <a href="#" class="badge badge-danger">Danger</a>
  <a href="#" class="badge badge-warning">Warning</a>
  <a href="#" class="badge badge-info">Info</a>
  <a href="#" class="badge badge-light">Light</a>
  <a href="#" class="badge badge-dark">Dark</a>

  <h4>ぱんくず</h4>
  <nav aria-label="breadcrumb" role="navigation">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Home</a></li>
      <li class="breadcrumb-item"><a href="#">Library</a></li>
      <li class="breadcrumb-item active" aria-current="page">Data</li>
    </ol>
  </nav>

  <h4>ボタン</h4>
  <button type="button" class="btn btn-primary">Primary</button>
  <button type="button" class="btn btn-secondary">Secondary</button>
  <button type="button" class="btn btn-success">Success</button>
  <button type="button" class="btn btn-danger">Danger</button>
  <button type="button" class="btn btn-warning">Warning</button>
  <button type="button" class="btn btn-info">Info</button>
  <button type="button" class="btn btn-light">Light</button>
  <button type="button" class="btn btn-dark">Dark</button>
  <button type="button" class="btn btn-link">Link</button>
  <hr>
  <a class="btn btn-primary" href="#" role="button">Link</a>
  <button class="btn btn-primary" type="submit">Button</button>
  <input class="btn btn-primary" type="button" value="Input">
  <input class="btn btn-primary" type="submit" value="Submit">
  <input class="btn btn-primary" type="reset" value="Reset">
  <hr>
  <button type="button" class="btn btn-outline-primary">Primary</button>
  <button type="button" class="btn btn-outline-secondary">Secondary</button>
  <button type="button" class="btn btn-outline-success">Success</button>
  <button type="button" class="btn btn-outline-danger">Danger</button>
  <button type="button" class="btn btn-outline-warning">Warning</button>
  <button type="button" class="btn btn-outline-info">Info</button>
  <button type="button" class="btn btn-outline-light">Light</button>
  <button type="button" class="btn btn-outline-dark">Dark</button>
  <hr>
  <button type="button" class="btn btn-primary btn-lg">Large button</button>
  <button type="button" class="btn btn-primary ">Large button</button>
  <button type="button" class="btn btn-primary btn-sm">Small button</button>
  <button type="button" class="btn btn-primary btn-xs">XSmall button</button>
  <hr>
  <button type="button" class="btn btn-primary btn-lg btn-block">Block level button</button>
  <button type="button" class="btn btn-secondary btn-lg btn-block">Block level button</button>

  <h4>ボタン（アクティブ）</h4>
  <a href="#" class="btn btn-primary active" role="button" aria-pressed="true">Primary link</a>
  <a href="#" class="btn btn-secondary active" role="button" aria-pressed="true">Link</a>
  <a href="#" class="btn btn-success active" role="button" aria-pressed="true">Link</a>
  <a href="#" class="btn btn-info active" role="button" aria-pressed="true">Link</a>
  <a href="#" class="btn btn-warning active" role="button" aria-pressed="true">Link</a>
  <a href="#" class="btn btn-danger active" role="button" aria-pressed="true">Link</a>

  <h4>ボタン（無効）</h4>
  <button type="button" class="btn btn-primary" disabled>Primary button</button>
  <button type="button" class="btn btn-secondary" disabled>Button</button>
  <button type="button" class="btn btn-success" disabled>Button</button>
  <button type="button" class="btn btn-info" disabled>Button</button>
  <button type="button" class="btn btn-warning" disabled>Button</button>
  <button type="button" class="btn btn-danger" disabled>Button</button>

  <h4>グループボタン</h4>
  <div class="btn-group" role="group" aria-label="Basic example">
    <button type="button" class="btn btn-lg btn-secondary">Left</button>
    <button type="button" class="btn btn-lg btn-secondary">Middle</button>
    <button type="button" class="btn btn-lg btn-secondary">Right</button>
  </div>
  <div class="btn-group" role="group" aria-label="Basic example">
    <button type="button" class="btn btn-secondary">Left</button>
    <button type="button" class="btn btn-secondary">Middle</button>
    <button type="button" class="btn btn-secondary">Right</button>
  </div>

  <div class="btn-group" role="group" aria-label="Basic example">
    <button type="button" class="btn btn-sm btn-secondary">Left</button>
    <button type="button" class="btn btn-sm btn-secondary">Middle</button>
    <button type="button" class="btn btn-sm btn-secondary">Right</button>
  </div>
  <div class="btn-group" role="group" aria-label="Basic example">
    <button type="button" class="btn btn-xs btn-secondary">Left</button>
    <button type="button" class="btn btn-xs btn-secondary">Middle</button>
    <button type="button" class="btn btn-xs btn-secondary">Right</button>
  </div>

  <hr>
  <div class="btn-group-vertical" role="group" aria-label="Vertical button group">
      <button type="button" class="btn btn-secondary">Button</button>
      <button type="button" class="btn btn-secondary">Button</button>
      <button type="button" class="btn btn-secondary">Button</button>
      <button type="button" class="btn btn-secondary">Button</button>
      <button type="button" class="btn btn-secondary">Button</button>
      <button type="button" class="btn btn-secondary">Button</button>
    </div>

  <h4>ドロップダウンボタン</h4>
  <div class="btn-group">
    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Action
    </button>
    <div class="dropdown-menu">
    	<h6 class="dropdown-header">Dropdown header</h6>
      <a class="dropdown-item" href="#">Action</a>
      <a class="dropdown-item" href="#">Another action</a>
      <a class="dropdown-item" href="#">Something else here</a>
      <a class="dropdown-item disabled" href="#">Disabled link</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="#">Separated link</a>
    </div>
  </div>
  <div class="btn-group">
    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Action
    </button>
    <div class="dropdown-menu">
    	<h6 class="dropdown-header">Dropdown header</h6>
      <a class="dropdown-item" href="#">Action</a>
      <a class="dropdown-item" href="#">Another action</a>
      <a class="dropdown-item" href="#">Something else here</a>
      <a class="dropdown-item disabled" href="#">Disabled link</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="#">Separated link</a>
    </div>
  </div>
  <div class="btn-group">
    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Action
    </button>
    <div class="dropdown-menu">
    	<h6 class="dropdown-header">Dropdown header</h6>
      <a class="dropdown-item" href="#">Action</a>
      <a class="dropdown-item" href="#">Another action</a>
      <a class="dropdown-item" href="#">Something else here</a>
      <a class="dropdown-item disabled" href="#">Disabled link</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="#">Separated link</a>
    </div>
  </div>
  <div class="btn-group">
    <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Action
    </button>
    <div class="dropdown-menu">
    	<h6 class="dropdown-header">Dropdown header</h6>
      <a class="dropdown-item" href="#">Action</a>
      <a class="dropdown-item" href="#">Another action</a>
      <a class="dropdown-item" href="#">Something else here</a>
      <a class="dropdown-item disabled" href="#">Disabled link</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="#">Separated link</a>
    </div>
  </div>
  <div class="btn-group">
    <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Action
    </button>
    <div class="dropdown-menu">
    	<h6 class="dropdown-header">Dropdown header</h6>
      <a class="dropdown-item" href="#">Action</a>
      <a class="dropdown-item" href="#">Another action</a>
      <a class="dropdown-item" href="#">Something else here</a>
      <a class="dropdown-item disabled" href="#">Disabled link</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="#">Separated link</a>
    </div>
  </div>
  <div class="btn-group">
    <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Action
    </button>
    <div class="dropdown-menu">
    	<h6 class="dropdown-header">Dropdown header</h6>
      <a class="dropdown-item" href="#">Action</a>
      <a class="dropdown-item" href="#">Another action</a>
      <a class="dropdown-item" href="#">Something else here</a>
      <a class="dropdown-item disabled" href="#">Disabled link</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="#">Separated link</a>
    </div>
  </div>
  <hr>
  <div class="btn-group">
    <button type="button" class="btn btn-primary btn-lg dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Action
    </button>
    <div class="dropdown-menu">
    	<h6 class="dropdown-header">Dropdown header</h6>
      <a class="dropdown-item" href="#">Action</a>
      <a class="dropdown-item" href="#">Another action</a>
      <a class="dropdown-item" href="#">Something else here</a>
      <a class="dropdown-item disabled" href="#">Disabled link</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="#">Separated link</a>
    </div>
  </div>
  <div class="btn-group">
    <button type="button" class="btn btn-primary btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Action
    </button>
    <div class="dropdown-menu">
    	<h6 class="dropdown-header">Dropdown header</h6>
      <a class="dropdown-item" href="#">Action</a>
      <a class="dropdown-item" href="#">Another action</a>
      <a class="dropdown-item" href="#">Something else here</a>
      <a class="dropdown-item disabled" href="#">Disabled link</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="#">Separated link</a>
    </div>
  </div>
  <div class="btn-group">
    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Action
    </button>
    <div class="dropdown-menu">
    	<h6 class="dropdown-header">Dropdown header</h6>
      <a class="dropdown-item" href="#">Action</a>
      <a class="dropdown-item" href="#">Another action</a>
      <a class="dropdown-item" href="#">Something else here</a>
      <a class="dropdown-item disabled" href="#">Disabled link</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="#">Separated link</a>
    </div>
  </div>
  <div class="btn-group">
    <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Action
    </button>
    <div class="dropdown-menu">
    	<h6 class="dropdown-header">Dropdown header</h6>
      <a class="dropdown-item" href="#">Action</a>
      <a class="dropdown-item" href="#">Another action</a>
      <a class="dropdown-item" href="#">Something else here</a>
      <a class="dropdown-item disabled" href="#">Disabled link</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="#">Separated link</a>
    </div>
  </div>

  <hr>
  <div class="clearfix">
  <div class="dropdown-menu" style="display: block; position: relative; z-index:0">
  <h6 class="dropdown-header">Dropdown header</h6>
    <a class="dropdown-item" href="#">Action</a>
    <a class="dropdown-item" href="#">Another action</a>
    <a class="dropdown-item" href="#">Something else here</a>
    <a class="dropdown-item disabled" href="#">Disabled link</a>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item" href="#">Separated link</a>
  </div>
  </div>

  <h4>アコーディオン</h4>
  <div id="accordion" role="tablist">
    <div class="card">
      <div class="card-header" role="tab" id="headingOne">
        <h5 class="mb-0">
          <a data-toggle="collapse" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Collapsible Group Item #1
          </a>
        </h5>
      </div>

      <div id="collapseOne" class="collapse show" role="tabpanel" aria-labelledby="headingOne" data-parent="#accordion">
        <div class="card-body">
          Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-header" role="tab" id="headingTwo">
        <h5 class="mb-0">
          <a class="collapsed" data-toggle="collapse" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
            Collapsible Group Item #2
          </a>
        </h5>
      </div>
      <div id="collapseTwo" class="collapse" role="tabpanel" aria-labelledby="headingTwo" data-parent="#accordion">
        <div class="card-body">
          Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-header" role="tab" id="headingThree">
        <h5 class="mb-0">
          <a class="collapsed" data-toggle="collapse" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
            Collapsible Group Item #3
          </a>
        </h5>
      </div>
      <div id="collapseThree" class="collapse" role="tabpanel" aria-labelledby="headingThree" data-parent="#accordion">
        <div class="card-body">
          Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
        </div>
      </div>
    </div>
  </div>

  <h4>リストグループ</h4>
  <ul class="list-group">
    <li class="list-group-item active">Cras justo odio<span class="badge badge-primary badge-pill">badge</span></li>
    <li class="list-group-item disabled">Dapibus ac facilisis in</li>
    <li class="list-group-item">Morbi leo risus</li>
    <li class="list-group-item">Porta ac consectetur ac</li>
    <li class="list-group-item">Vestibulum at eros</li>
  </ul>
  <div class="list-group mt-3">
    <button type="button" class="list-group-item list-group-item-action active">
      Cras justo odio
    </button>
    <button type="button" class="list-group-item list-group-item-action">Dapibus ac facilisis in <span class="badge badge-primary badge-pill">badge</span></button>
    <button type="button" class="list-group-item list-group-item-action">Morbi leo risus</button>
    <button type="button" class="list-group-item list-group-item-action">Porta ac consectetur ac</button>
    <button type="button" class="list-group-item list-group-item-action" disabled>Vestibulum at eros</button>
  </div>

  <ul class="list-group mt-3">
    <li class="list-group-item">Dapibus ac facilisis in</li>
    <li class="list-group-item list-group-item-primary">This is a primary list group item <span class="badge badge-primary badge-pill">badge</span></li>
    <li class="list-group-item list-group-item-secondary">This is a secondary list group item</li>
    <li class="list-group-item list-group-item-success">This is a success list group item</li>
    <li class="list-group-item list-group-item-danger">This is a danger list group item</li>
    <li class="list-group-item list-group-item-warning">This is a warning list group item</li>
    <li class="list-group-item list-group-item-info">This is a info list group item</li>
    <li class="list-group-item list-group-item-light">This is a light list group item</li>
    <li class="list-group-item list-group-item-dark">This is a dark list group item</li>
  </ul>

  <h4>モーダル</h4>
  <div class="modal" tabindex="-1" role="dialog" style="position: relative; top: auto; right: auto; bottom: auto;  left: auto; z-index: 1; display: block;">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Modal title</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Modal body text goes here.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary">Save changes</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <h4>タブ</h4>
  <ul class="nav nav-tabs">
    <li class="nav-item">
      <a class="nav-link active" href="#">Active</a>
    </li>
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Dropdown</a>
      <div class="dropdown-menu">
        <a class="dropdown-item" href="#">Action</a>
        <a class="dropdown-item" href="#">Another action</a>
        <a class="dropdown-item" href="#">Something else here</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="#">Separated link</a>
      </div>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="#">Link</a>
    </li>
    <li class="nav-item">
      <a class="nav-link disabled" href="#">Disabled</a>
    </li>
  </ul>

  <h4>アラート</h4>
  <div class="alert alert-primary alert-dismissible" role="alert">
    This is a primary alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <div class="alert alert-secondary alert-dismissible" role="alert">
    This is a secondary alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <div class="alert alert-success alert-dismissible" role="alert">
    This is a success alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <div class="alert alert-danger alert-dismissible" role="alert">
    This is a danger alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <div class="alert alert-warning alert-dismissible" role="alert">
    This is a warning alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <div class="alert alert-info alert-dismissible" role="alert">
    This is a info alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <div class="alert alert-light alert-dismissible" role="alert">
    This is a light alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <div class="alert alert-dark alert-dismissible" role="alert">
    This is a dark alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>

  <h4>ポップオーバー</h4>
  <div class="position: relative;">
  <div class="bd-example bd-example-popover-static">
    <div class="popover bs-popover-top bs-popover-top-docs">
      <div class="arrow"></div>
      <h3 class="popover-header">Popover top</h3>
      <div class="popover-body">
        <p>Sed posuere consectetur est at lobortis. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum.</p>
      </div>
    </div>

    <div class="popover bs-popover-right bs-popover-right-docs">
      <div class="arrow"></div>
      <h3 class="popover-header">Popover right</h3>
      <div class="popover-body">
        <p>Sed posuere consectetur est at lobortis. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum.</p>
      </div>
    </div>

    <div class="popover bs-popover-bottom bs-popover-bottom-docs">
      <div class="arrow"></div>
      <h3 class="popover-header">Popover bottom</h3>
      <div class="popover-body">
        <p>Sed posuere consectetur est at lobortis. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum.</p>
      </div>
    </div>

    <div class="popover bs-popover-left bs-popover-left-docs">
      <div class="arrow"></div>
      <h3 class="popover-header">Popover left</h3>
      <div class="popover-body">
        <p>Sed posuere consectetur est at lobortis. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum.</p>
      </div>
    </div>

    <div class="clearfix"></div>
  </div>
  </div>

  <h4>ツールチップ</h4>
  <div class="bd-example bd-example-tooltip-static">
    <div class="tooltip bs-tooltip-top bs-tooltip-top-docs" role="tooltip">
      <div class="arrow"></div>
      <div class="tooltip-inner">
        Tooltip on the top
      </div>
    </div>
    <div class="tooltip bs-tooltip-right bs-tooltip-right-docs" role="tooltip">
      <div class="arrow"></div>
      <div class="tooltip-inner">
        Tooltip on the right
      </div>
    </div>
    <div class="tooltip bs-tooltip-bottom bs-tooltip-bottom-docs" role="tooltip">
      <div class="arrow"></div>
      <div class="tooltip-inner">
        Tooltip on the bottom
      </div>
    </div>
    <div class="tooltip bs-tooltip-left bs-tooltip-left-docs" role="tooltip">
      <div class="arrow"></div>
      <div class="tooltip-inner">
        Tooltip on the left
      </div>
    </div>
  </div>

  <h4>プログレスバー</h4>
  <div class="progress mt-2">
    <div class="progress-bar" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">25%</div>
  </div>
  <div class="progress mt-2">
    <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
  </div>
  <div class="progress mt-2">
    <div class="progress-bar bg-info" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
  </div>
  <div class="progress mt-2">
    <div class="progress-bar bg-warning" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
  </div>
  <div class="progress mt-2">
    <div class="progress-bar bg-danger" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
  </div>

  <h4>フォーム要素</h4>
  <form>
    <div class="form-group">
      <input type="email" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com">
    </div>
    <div class="form-group">
      <select class="form-control" id="exampleFormControlSelect1">
        <option>1</option>
        <option>2</option>
        <option>3</option>
        <option>4</option>
        <option>5</option>
      </select>
    </div>
    <div class="form-group">
      <select multiple class="form-control" id="exampleFormControlSelect2">
        <option>1</option>
        <option>2</option>
        <option>3</option>
        <option>4</option>
        <option>5</option>
      </select>
    </div>
    <div class="form-group">
      <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
    </div>
    <div class="form-group">
      <input type="file" class="form-control-file" id="exampleFormControlFile1">
    </div>

    <div class="form-check">
    <label class="form-check-label">
      <input class="form-check-input" type="checkbox" value="">
      Option one is this and that&mdash;be sure to include why it's great
    </label>
  </div>
  <div class="form-check disabled">
    <label class="form-check-label">
      <input class="form-check-input" type="checkbox" value="" disabled>
      Option two is disabled
    </label>
  </div>
  <div class="form-check">
    <label class="form-check-label">
      <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="option1" checked>
      Option one is this and that&mdash;be sure to include why it's great
    </label>
  </div>
  <div class="form-check">
    <label class="form-check-label">
      <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="option2">
      Option two can be something else and selecting it will deselect option one
    </label>
  </div>
  <div class="form-check disabled">
    <label class="form-check-label">
      <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios3" value="option3" disabled>
      Option three is disabled
    </label>
  </div>
  <small id="passwordHelpBlock" class="form-text text-muted">
    注釈文がここにはいります。注釈文がここにはいります。注釈文がここにはいります。注釈文がここにはいります。注釈文がここにはいります。
  </small>
  </form>
  <hr>
  <div class="container-fluid">
  	<div class="row form-group">
  		<input class="form-control form-control-lg" type="text" placeholder=".form-control-lg">
  	</div>
  	<div class="row form-group">
  		<input class="form-control" type="text" placeholder="Default input">
  	</div>
  	<div class="row form-group">
  		<input class="form-control form-control-sm" type="text" placeholder=".form-control-sm">
  	</div>
  	<div class="row form-group">
  		<select class="col-4 form-control form-control-lg">
  		  <option>Large select</option>
  		</select>
  		<select class="col-4 form-control">
  		  <option>Default select</option>
  		</select>
  		<select class="col-4 form-control form-control-sm">
  		  <option>Small select</option>
  		</select>
  	</div>
  </div>

  <h4>読み取り専用</h4>
  <div class="container-fluid">
  	<div class="row form-group">
  		<input class="form-control" type="text" placeholder="Readonly input here…" readonly>
  	</div>
  	<div class="row form-group">
  		<textarea class="form-control" id="exampleFormControlTextarea1" rows="3" readonly></textarea>
  	</div>
  </div>

  <h4>無効</h4>
  <div class="container-fluid">
  	<div class="row form-group">
  		<input class="form-control" type="text" placeholder="Disabled input here…" disabled>
  	</div>
  	<div class="row form-group">
  		<textarea class="form-control" id="exampleFormControlTextarea1" rows="3" disabled></textarea>
  	</div>
      <div class="row form-group">
        <select id="disabledSelect" class="form-control" disabled>
          <option>Disabled select</option>
        </select>
      </div>

  </div>

  <h4>エラー</h4>
  <form>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="validationServer01">First name</label>
        <input type="text" class="form-control is-valid" id="validationServer01" placeholder="First name" value="Mark" required>
      </div>
      <div class="col-md-6 mb-3">
        <label for="validationServer02">Last name</label>
        <input type="text" class="form-control is-valid" id="validationServer02" placeholder="Last name" value="Otto" required>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="validationServer03">City</label>
        <input type="text" class="form-control is-invalid" id="validationServer03" placeholder="City" required>
        <div class="invalid-feedback">
          Please provide a valid city.
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <label for="validationServer04">State</label>
        <input type="text" class="form-control is-invalid" id="validationServer04" placeholder="State" required>
        <div class="invalid-feedback">
          Please provide a valid state.
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <label for="validationServer05">Zip</label>
        <input type="text" class="form-control is-invalid" id="validationServer05" placeholder="Zip" required>
        <div class="invalid-feedback">
          Please provide a valid zip.
        </div>
      </div>
      <div class="col-md-3">
      	<select id="disabledSelect" class="form-control is-invalid">
  	        <option>select</option>
  	      </select>
      </div>
    </div>
  </form>

  <h4>ボタン拡張</h4>
  <div class="row">
    <div class="col-lg-6">
      <div class="input-group">
        <span class="input-group-btn">
          <button class="btn btn-secondary" type="button">Go!</button>
        </span>
        <input type="text" class="form-control" placeholder="Search for..." aria-label="Search for...">
      </div>
    </div>
    <div class="col-lg-6">
      <div class="input-group">
        <input type="text" class="form-control" placeholder="Search for..." aria-label="Search for...">
        <span class="input-group-btn">
          <button class="btn btn-secondary" type="button">Go!</button>
        </span>
      </div>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-lg-6 offset-lg-3">
      <div class="input-group">
        <span class="input-group-btn">
          <button class="btn btn-secondary" type="button">Hate it</button>
        </span>
        <input type="text" class="form-control" placeholder="Product name" aria-label="Product name">
        <span class="input-group-btn">
          <button class="btn btn-primary" type="button">Love it</button>
        </span>
      </div>
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-lg-6">
      <div class="input-group">
        <div class="input-group-btn">
          <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Action
          </button>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="#">Action</a>
            <a class="dropdown-item" href="#">Another action</a>
            <a class="dropdown-item" href="#">Something else here</a>
            <div role="separator" class="dropdown-divider"></div>
            <a class="dropdown-item" href="#">Separated link</a>
          </div>
        </div>
        <input type="text" class="form-control" aria-label="Text input with dropdown button">
      </div>
    </div>
    <div class="col-lg-6">
      <div class="input-group">
        <input type="text" class="form-control" aria-label="Text input with dropdown button">
        <div class="input-group-btn">
          <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Action
          </button>
          <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="#">Action</a>
            <a class="dropdown-item" href="#">Another action</a>
            <a class="dropdown-item" href="#">Something else here</a>
            <div role="separator" class="dropdown-divider"></div>
            <a class="dropdown-item" href="#">Separated link</a>
          </div>
        </div>
      </div>
    </div>
  </div>


  </div>
  <?php endif?>

</section>
