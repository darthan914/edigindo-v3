<!-- top navigation -->
<div class="top_nav">
	<div class="nav_menu">
		<nav class="" role="navigation">
			<div class="nav toggle">
				<a id="menu_toggle"><i class="fa fa-bars"></i></a>
			</div>

			<ul class="nav navbar-nav navbar-right">
				<li class="">
					<a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						<img src="{{ asset(Auth::user()->photo != '' ? Auth::user()->photo : 'backend/images/user.png') }}" alt="">Welcome, {{ Auth::user()->fullname }}
						<span class=" fa fa-angle-down"></span>
					</a>
					<ul class="dropdown-menu dropdown-usermenu pull-right">
						<li><a href="{{ route('backend.user.profile') }}"> Profile</a></li>
						@if(Auth::user()->isImpersonating())
						<li><a href="{{ route('backend.user.leave') }}"><i class="fa fa-sign-out pull-right"></i> Leave from {{ Auth::user()->fullname }}</a></li>
						@else
						<li><a href="{{ route('backend.logout') }}"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
						@endif
					</ul>
				</li>
				<notification v-bind:htmlunreadcount="htmlunreadcount"></notification>
				
			</ul>


		</nav>
	</div>
</div>
<!-- /top navigation -->
