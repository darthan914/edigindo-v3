<div class="col-md-3 left_col menu_fixed">
	<div class="left_col scroll-view">
		<div class="navbar nav_title" style="border: 0;">
			<a href="{{ route('backend.home') }}" class="site_title"> <span>EDigindo</span></a>
		</div>

		<div class="clearfix"></div>

		<div class="profile">
			<div class="profile_pic">
				<img src="{{ asset(Auth::user()->photo != '' ? Auth::user()->photo : 'backend/images/user.png') }}" alt="..." class="img-circle profile_img">
			</div>
			<div class="profile_info">
				<span>Hai,</span>
				<h2>{{ Auth::user()->nickname }}</h2>
			</div>
		</div>

		<br />

		<!-- sidebar menu -->
		<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
			<div class="menu_section active">
				<h3>General</h3>
				
				
				<ul class="nav side-menu">
					<li class="{{ Route::is('backend.home*') ? 'active' : '' }}">
						<a href="{{ route('backend.home') }}"><i class="fa fa-home"></i>Home</a>
					</li>

					@if(Gate::check('list-activity') || Gate::check('list-dayoff') || Gate::check('list-absence') || Gate::check('list-car') || Gate::check('list-advertisment'))
					<li>
						<a><i class="fa fa-sitemap"></i> HRD <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							@can('list-activity')
							<li class="{{ Route::is('backend.activity*') ? 'active' : '' }}"><a href="{{ route('backend.activity') }}">{{-- <i class="fa fa-clock-o"></i> --}}Activity</a></li>
							@endcan

							@can('list-dayoff')
							<li class="{{ Route::is('backend.dayoff*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-calendar-minus-o"></i> --}}Leave<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.dayoff.index*') ? 'active' : '' }}"><a href="{{ Route('backend.dayoff') }}">List</a></li>
									@can('setting-dayoff')
									<li class="{{ Route::is('backend.dayoff.setting*') ? 'active' : '' }}"><a href="{{ Route('backend.dayoff.setting') }}">Setting</a></li>
									@endcan
								</ul>
							</li>
							@endcan

							@can('list-absence')
							<li class="{{ Route::is('backend.absence*') ? 'active' : '' }}"><a href="{{ route('backend.absence') }}">{{-- <i class="fa fa-minus-circle"></i> --}}Form Absence</a></li>
							@endcan

							@can('list-car')
							<li class="{{ Route::is('backend.car*') ? 'active' : '' }}"><a href="{{ route('backend.car') }}">{{-- <i class="fa fa-car"></i> --}}Car</a></li>
							@endcan

							@can('list-advertisment')
							<li class="{{ Route::is('backend.advertisment*') ? 'active' : '' }}"><a href="{{ route('backend.advertisment') }}">{{-- <i class="fa fa-bus"></i> --}}Advertisment</a></li>
							@endcan

							@can('list-jobApply')
							<li class="{{ Route::is('backend.jobApply*') ? 'active' : '' }}"><a href="{{ route('backend.jobApply') }}">{{-- <i class="fa fa-bus"></i> --}}Job Apply</a></li>
							@endcan

							@can('list-quote')
							<li class="{{ Route::is('backend.quote*') ? 'active' : '' }}"><a href="{{ route('backend.quote') }}">{{-- <i class="fa fa-bus"></i> --}}Quote</a></li>
							@endcan
						</ul>
					</li>
					@endif

					@if(Gate::check('list-company')||Gate::check('list-arModel'))
					<li>
						<a><i class="fa fa-sitemap"></i> Database <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							@can('list-company')
							<li class="{{ Route::is('backend.company*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-building"></i> --}}Company<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.company.index*') ? 'active' : '' }}"><a href="{{ Route('backend.company') }}">List</a></li>
									@can('dashboard-company')
									<li class="{{ Route::is('backend.company.dashboard*') ? 'active' : '' }}"><a href="{{ Route('backend.company.dashboard') }}">Dashboard</a></li>
									@endcan
								</ul>
							</li>
							@endcan

							@can('list-arModel')
							<li class="{{ Route::is('backend.arModel*') ? 'active' : '' }}"><a href="{{ route('backend.arModel') }}">{{-- <i class="fa fa-bus"></i> --}}Ar Model</a></li>
							@endcan
						</ul>
					</li>
					@endif

					@if(Gate::check('list-target')||Gate::check('list-campaign')||Gate::check('list-contract')||Gate::check('list-crm')||Gate::check('list-spk')||Gate::check('list-offer')||Gate::check('list-todo'))

					<li>
						<a><i class="fa fa-sitemap"></i> Sales <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							@can('list-target')
							<li class="{{ Route::is('backend.target.index*') ? 'active' : '' }}"><a href="{{ Route('backend.target') }}">Target</a></li>
		                  	@endcan

		                  	@can('list-campaign')
							<li class="{{ Route::is('backend.campaign.index*') ? 'active' : '' }}"><a href="{{ Route('backend.campaign') }}">Campaign</a></li>
		                  	@endcan

		                  	@can('list-contract')
							<li class="{{ Route::is('backend.contract*') ? 'active' : '' }}"><a href="{{ route('backend.contract') }}">{{-- <i class="fa fa-check-square"></i> --}}Contract</a></li>
							@endcan

							@can('list-crm')
							<li class="{{ Route::is('backend.crm*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-th-list"></i> --}}CRM<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.crm.index*') ? 'active' : '' }}"><a href="{{ Route('backend.crm') }}">List</a></li>
								</ul>
							</li>
							@endcan

							@can('list-spk')
							<li class="{{ Route::is('backend.spk*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-file-text"></i> --}}SPK<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.spk.index*') ? 'active' : '' }}"><a href="{{ Route('backend.spk') }}">List</a></li>
									@can('dashboard-spk')
									<li class="{{ Route::is('backend.spk.dashboard*') ? 'active' : '' }}"><a href="{{ Route('backend.spk.dashboard') }}">Dashboard</a></li>
									@endcan
								</ul>
							</li>
							@endcan

							@can('list-offer')
							<li class="{{ Route::is('backend.offer*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-handshake-o"></i> --}}Quotation<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.offer.index*') ? 'active' : '' }}"><a href="{{ Route('backend.offer') }}">List</a></li>
									@can('dashboard-offer')
									<li class="{{ Route::is('backend.offer.dashboard*') ? 'active' : '' }}"><a href="{{ Route('backend.offer.dashboard') }}">Dashboard</a></li>
									@endcan
								</ul>
							</li>
							@endcan

							

							@can('list-todo')
							<li class="{{ Route::is('backend.todo*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-th-list"></i> --}}To Do / CRM<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.todo.index*') ? 'active' : '' }}"><a href="{{ Route('backend.todo') }}">List</a></li>
									<li class="{{ Route::is('backend.todo.calendar*') ? 'active' : '' }}"><a href="{{ Route('backend.todo.calendar') }}">Calendar</a></li>
									@can('dashboard-todo')
									<li class="{{ Route::is('backend.todo.dashboard*') ? 'active' : '' }}"><a href="{{ Route('backend.todo.dashboard') }}">Dashboard</a></li>
									@endcan
								</ul>
							</li>
							@endcan
						</ul>
					</li>
					@endif

					@if(Gate::check('list-production'))

					<li>
						<a><i class="fa fa-sitemap"></i> Operational <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							@can('list-production')
							<li class="{{ Route::is('backend.production*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-cube"></i> --}}Production<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.production.index*') ? 'active' : '' }}"><a href="{{ Route('backend.production') }}">List</a></li>
									<li class="{{ Route::is('backend.production.calendar*') ? 'active' : '' }}"><a href="{{ Route('backend.production.calendar') }}">Calendar</a></li>
								</ul>
							</li>
							@endcan
						</ul>
					</li>
					@endif

					@if(Gate::check('list-estimator')||Gate::check('list-invoice'))

					<li>
						<a><i class="fa fa-sitemap"></i> Financial & Admin <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							@can('list-estimator')
							<li class="{{ Route::is('backend.estimator*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-commenting"></i> --}}Estimator<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.estimator.index*') ? 'active' : '' }}"><a href="{{ Route('backend.estimator') }}">List</a></li>
									@can('dashboard-estimator')
									<li class="{{ Route::is('backend.estimator.dashboard*') ? 'active' : '' }}"><a href="{{ Route('backend.estimator.dashboard') }}">Dashboard</a></li>
									@endcan
								</ul>
							</li>
		                  	@endcan

							@can('list-invoice')
							<li class="{{ Route::is('backend.invoice*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-usd"></i> --}}Invoice / SPK Recap<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.invoice.index*') ? 'active' : '' }}"><a href="{{ Route('backend.invoice') }}">List</a></li>
									@can('dashboard-invoice')
									<li class="{{ Route::is('backend.invoice.dashboard*') ? 'active' : '' }}"><a href="{{ Route('backend.invoice.dashboard') }}">Dashboard</a></li>
									@endcan
								</ul>
							</li>
							@endcan
						</ul>
					</li>
					@endif

					@if(Gate::check('list-delivery')||Gate::check('list-stock'))

					<li>
						<a><i class="fa fa-sitemap"></i> Logistic <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							@can('list-delivery')
							<li class="{{ Route::is('backend.delivery*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-envelope"></i> --}}Delivery<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.delivery.index*') ? 'active' : '' }}"><a href="{{ Route('backend.delivery') }}">List</a></li>
									
									@can('courier-delivery')
									<li class="{{ Route::is('backend.delivery.courier*') ? 'active' : '' }}"><a href="{{ Route('backend.delivery.courier') }}">Courier</a></li>
									@endcan

									@can('viewDist-delivery')
									<li class="{{ Route::is('backend.delivery.viewDistance*') ? 'active' : '' }}"><a href="{{ Route('backend.delivery.viewDistance') }}">View Distance Client</a></li>
									@endcan
								</ul>
							</li>
							@endcan

							@can('list-stock')
							<li class="{{ Route::is('backend.stock*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-envelope"></i> --}}Stock Item<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.stock.index*') ? 'active' : '' }}"><a href="{{ Route('backend.stock') }}">List</a></li>
									
									<li class="{{ Route::is('backend.stock.stockBook*') ? 'active' : '' }}"><a href="{{ Route('backend.stock.stockBook') }}">Book</a></li>
									@can('listStockPlace-stock')
									<li class="{{ Route::is('backend.stock.stockPlace*') ? 'active' : '' }}"><a href="{{ Route('backend.stock.stockPlace') }}">Place</a></li>
									@endcan
								</ul>
							</li>
							@endcan
						</ul>
					</li>
					@endif

					@if(Gate::check('list-listRequest')||Gate::check('list-pr')||Gate::check('list-supplier'))

					<li>
						<a><i class="fa fa-sitemap"></i> Purchasing <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							@can('list-listRequest')
							<li class="{{ Route::is('backend.listRequest*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-question"></i> --}}Request<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.listRequest.index*') ? 'active' : '' }}"><a href="{{ Route('backend.listRequest') }}">List</a></li>
								</ul>
								
							</li>
		                  	@endcan

		                  	@can('list-pr')
							<li class="{{ Route::is('backend.pr*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-shopping-basket"></i> --}}PR<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.pr.index*') ? 'active' : '' }}"><a href="{{ Route('backend.pr') }}">List</a></li>
									
									<li class="{{ Route::is('backend.pr.unconfirm*') ? 'active' : '' }}"><a href="{{ Route('backend.pr.unconfirm') }}">Unconfirm</a></li>

									@can('confirmList-pr')
									<li class="{{ Route::is('backend.pr.confirm*') ? 'active' : '' }}"><a href="{{ Route('backend.pr.confirm') }}">Confirm</a></li>
									@endcan

									@can('dashboard-pr')
									<li class="{{ Route::is('backend.pr.dashboard*') ? 'active' : '' }}"><a href="{{ Route('backend.pr.dashboard') }}">Dashboard</a></li>
									@endcan
								</ul>
							</li>
							@endcan
							

							@can('list-supplier')
							<li class="{{ Route::is('backend.supplier*') ? 'active' : '' }}"><a href="{{ route('backend.supplier') }}">{{-- <i class="fa fa-truck"></i> --}}Supplier</a></li>
							@endcan
						</ul>
					</li>
					@endif

					@if(Gate::check('list-designer'))
					<li>
						<a><i class="fa fa-sitemap"></i> Designer <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							@can('list-designer')
							<li class="{{ Route::is('backend.designer*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-lemon-o"></i> --}}Designer<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.designer.index*') ? 'active' : '' }}"><a href="{{ Route('backend.designer') }}">List</a></li>
									@can('designCandidate-designer')
									<li class="{{ Route::is('backend.designer.designCandidate*') ? 'active' : '' }}"><a href="{{ Route('backend.designer.designCandidate') }}">Design Request</a></li>
									@endcan
									<li class="{{ Route::is('backend.designer.calendar*') ? 'active' : '' }}"><a href="{{ Route('backend.designer.calendar') }}">Calendar</a></li>
									@can('dashboard-designer')
									<li class="{{ Route::is('backend.designer.dashboard*') ? 'active' : '' }}"><a href="{{ Route('backend.designer.dashboard') }}">Dashboard</a></li>
									@endcan

								</ul>
							</li>
							@endcan

							@can('list-designRequest')
							<li class="{{ Route::is('backend.designRequest*') ? 'active' : '' }}"><a href="{{ route('backend.designRequest') }}">{{-- <i class="fa fa-bookmark"></i> --}}My Request Design</a></li>
							@endcan
						</ul>
					</li>
					@endif

					@if(Gate::check('accountList-account'))
					<li>
						<a><i class="fa fa-sitemap"></i> Accounting <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							@can('accountList-account')
							<li class="{{ Route::is('backend.account*') ? 'active' : '' }}">
								<a>
									{{-- <i class="fa fa-money"></i> --}}Account<span class="fa fa-chevron-down"></span>
								</a>
								<ul class="nav child_menu" style="">
									<li class="{{ Route::is('backend.account.accountList*') ? 'active' : '' }}"><a href="{{ Route('backend.account.accountList') }}">List</a></li>
									
									@can('accountJournal-account')
									<li class="{{ Route::is('backend.account.accountJournal*') ? 'active' : '' }}"><a href="{{ Route('backend.account.accountJournal') }}">Journal</a></li>
									@endcan

									@can('accountSales-account')
									<li class="{{ Route::is('backend.account.accountSales*') ? 'active' : '' }}"><a href="{{ Route('backend.account.accountSales') }}">Sales</a></li>
									@endcan

									@can('accountBanking-account')
									<li class="{{ Route::is('backend.account.accountBanking*') ? 'active' : '' }}"><a href="{{ Route('backend.account.accountBanking') }}">Banking</a></li>
									@endcan

									@can('accountPurchasing-account')
									<li class="{{ Route::is('backend.account.accountPurchasing*') ? 'active' : '' }}"><a href="{{ Route('backend.account.accountPurchasing') }}">Purchase</a></li>
									@endcan

									@can('accountClass-account')
									<li class="{{ Route::is('backend.account.accountClass*') ? 'active' : '' }}"><a href="{{ Route('backend.account.accountClass') }}">Classification</a></li>
									@endcan

									@can('accountType-account')
									<li class="{{ Route::is('backend.account.accountType*') ? 'active' : '' }}"><a href="{{ Route('backend.account.accountType') }}">Type</a></li>
									@endcan

									
								</ul>
								
							</li>
							@endcan
						</ul>
					</li>
					@endif

					@if(Gate::check('labelPackage-tool'))
					<li>
						<a><i class="fa fa-sitemap"></i> Tools <span class="fa fa-chevron-down"></span></a>
						<ul class="nav child_menu">
							@can('labelPackage-tool')
		                  	<li class="{{ Route::is('backend.tool.labelPackage') ? 'active' : '' }}"><a href="{{ route('backend.tool.labelPackage') }}">Label Package</a></li>
		                  	@endcan
						</ul>
					</li>
					@endif
				</ul>
			</div>
			@if(Gate::check('list-user')||Gate::check('list-position')||Gate::check('list-division')||Gate::check('read-file')||Gate::check('config')||Gate::check('sql')||Gate::check('read-archive'))
			<div class="menu_section">
				<h3>Access</h3>
				<ul class="nav side-menu">

					@can('list-user')
                  	<li class="{{ Route::is('backend.user*') ? 'active' : '' }}"><a href="{{ route('backend.user') }}"><i class="fa fa-users"></i>User List</a></li>
                  	@endcan

                  	@can('list-position')
                  	<li class="{{ Route::is('backend.position*') ? 'active' : '' }}"><a href="{{ route('backend.position') }}"><i class="fa fa-map-signs"></i>Position List</a></li>
                  	@endcan

                  	@can('list-division')
                  	<li class="{{ Route::is('backend.division*') ? 'active' : '' }}"><a href="{{ route('backend.division') }}"><i class="fa fa-sitemap"></i>Division List</a></li>
                  	@endcan

                  	@can('read-file')
                  	<li class="{{ Route::is('backend.file*') ? 'active' : '' }}"><a href="{{ route('backend.file') }}"><i class="fa fa-home"></i>File Management</a></li>
                  	@endcan

                  	@can('configuration')
                  	<li class="{{ Route::is('backend.config*') ? 'active' : '' }}"><a href="{{ route('backend.config') }}"><i class="fa fa-cog"></i>Configuration</a></li>
                  	@endcan

                  	@can('sql')
                  	<li class="{{ Route::is('backend.sql*') ? 'active' : '' }}"><a href="{{ route('backend.sql') }}"><i class="fa fa-database"></i>SQL Database</a></li>
                  	@endcan

                  	@can('read-archive')
                  	<li class="{{ Route::is('backend.archive*') ? 'active' : '' }}"><a href="{{ route('backend.archive') }}"><i class="fa fa-archive"></i>Archive</a></li>
                  	@endcan

                  	{{-- <li class="{{ Route::is('backend.dummy.attandance') ? 'active' : '' }}"><a href="{{ route('backend.dummy.attandance') }}"><i class="fa fa-archive"></i>Test</a></li> --}}

				</ul>
			</div>
			@endif
		</div>
		<!-- /sidebar menu -->

		<!-- /menu footer buttons -->
		<div class="sidebar-footer hidden-small">
			@can('read-user')
			<a href="{{ route('backend.user.profile') }}" data-toggle="tooltip" data-placement="top" title="Prifiles">
				<span class="glyphicon glyphicon-user" aria-hidden="true"></span>
			</a>
			@endcan

			@cannot('read-user')
			<a href="#" data-toggle="tooltip" data-placement="top" title="">
				<span class="glyphicon glyphicon-user" aria-hidden="true"></span>
			</a>
			@endcannot
			
			<a href="{{ route('backend.notification.index') }}" data-toggle="tooltip" data-placement="top" title="Inbox">
				<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>
			</a>

			@can('config')
			<a href="{{ route('backend.config') }}" data-toggle="tooltip" data-placement="top" title="Configuration">
				<span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
			</a>
			@endcan

			@cannot('config')
			<a href="#" data-toggle="tooltip" data-placement="top" title="Configuration">
				<span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
			</a>
			@endcan

			@if(Auth::user()->isImpersonating())
				<a href="{{ route('backend.user.leave') }}" data-toggle="tooltip" data-placement="top" title="Leave">
					<span class="glyphicon glyphicon-off" aria-hidden="true"></span>
				</a>
			@else
				<a href="{{ route('backend.logout') }}" data-toggle="tooltip" data-placement="top" title="Logout">
					<span class="glyphicon glyphicon-off" aria-hidden="true"></span>
				</a>
			@endif
		</div>
		<!-- /menu footer buttons -->
	</div>
</div>
