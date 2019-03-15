@if (Session::has('success'))
<script>
  window.setTimeout(function() {
    $(".alert-success").fadeTo(700, 0).slideUp(700, function(){
        $(".row.success").remove();
    });
  }, 15000);
</script>
<div class="row success">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="alert alert-success alert-dismissible fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
      </button>
      <strong>Success : {!! Session::get('success') !!}</strong>
    </div>
  </div>
</div>
@endif

@if (Session::has('failed'))
<script>
  window.setTimeout(function() {
    $(".alert-danger").fadeTo(700, 0).slideUp(700, function(){
        $(".row.danger").remove();
    });
  }, 15000);
</script>
<div class="row danger">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="alert alert-danger alert-dismissible fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
      </button>
      <strong>Failed : {!! Session::get('failed') !!}</strong>
    </div>
  </div>
</div>
@endif

@if (Session::has('info'))
<script>
  window.setTimeout(function() {
    $(".alert-info").fadeTo(700, 0).slideUp(700, function(){
        $(".row.info").remove();
    });
  }, 15000);
</script>
<div class="row info">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="alert alert-info alert-dismissible fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
      </button>
      <strong>Info : {!! Session::get('info') !!}</strong>
    </div>
  </div>
</div>
@endif

@if (Session::has('warning'))
<script>
  window.setTimeout(function() {
    $(".alert-warning").fadeTo(700, 0).slideUp(700, function(){
        $(".row.warning").remove();
    });
  }, 15000);
</script>
<div class="row warning">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="alert alert-warning alert-dismissible fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
      </button>
      <strong>Warning : {!! Session::get('warning') !!}</strong>
    </div>
  </div>
</div>
@endif


<div style="position: fixed; top: 5em; right: 2em; z-index: 2000; width: 25em;">
  <info v-bind:notifcontent="notifcontent"></info>
</div>


