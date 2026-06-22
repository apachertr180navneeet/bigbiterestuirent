<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
	<div class="app-brand demo">
		<a href="{{route('admin.dashboard')}}" class="app-brand-link">
			<span class="app-brand-text demo menu-text fw-bold ms-2">{{ config('app.name') }}</span>
		</a>

		<a href="javascript:void(0);"
			class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
			<i class="bx bx-chevron-left bx-sm align-middle"></i>
		</a>
	</div>

	<div class="menu-inner-shadow"></div>

	<ul class="menu-inner py-1">
		<li class="menu-item {{ request()->is('admin/dashboard') ? 'active' : ''}}">
			<a href="{{route('admin.dashboard')}}" class="menu-link">
				<i class="menu-icon tf-icons bx bx-home-circle"></i>
				<div data-i18n="Dashboard">Dashboard</div>
			</a>
		</li>

		<li class="menu-item {{ request()->is('admin/salesperson') ? 'active' : ''}}">
			<a href="{{route('admin.salesperson.index')}}" class="menu-link">
				<i class="menu-icon tf-icons bx bx-home-circle"></i>
				<div data-i18n="Dashboard">Sale Person</div>
			</a>
		</li>


		<li class="menu-item {{ request()->is('admin/customer') ? 'active' : ''}}">
			<a href="{{route('admin.customer.index')}}" class="menu-link">
				<i class="menu-icon tf-icons bx bx-home-circle"></i>
				<div data-i18n="Dashboard">Firms</div>
			</a>
		</li>

		<li class="menu-item {{ request()->is('admin/invoice') ? 'active' : ''}}">
			<a href="{{route('admin.invoice.index')}}" class="menu-link">
				<i class="menu-icon tf-icons bx bx-home-circle"></i>
				<div data-i18n="Dashboard">Invoice</div>
			</a>
		</li>

		<li class="menu-item {{ request()->is('admin/receipt') ? 'active' : ''}}">
			<a href="{{route('admin.receipt.index')}}" class="menu-link">
				<i class="menu-icon tf-icons bx bx-home-circle"></i>
				<div data-i18n="Dashboard">Receipt</div>
			</a>
		</li>
		
		<li class="menu-item {{ request()->is('admin/sales-person-report') ? 'active' : ''}}">
			<a href="{{route('admin.sales.person.report')}}" class="menu-link">
				<i class="menu-icon tf-icons bx bx-home-circle"></i>
				<div data-i18n="Dashboard">Salesman Reort</div>
			</a>
		</li>

		<li class="menu-item {{ request()->is('admin/cash-report') ? 'active' : ''}}">
			<a href="{{route('admin.cash.report')}}" class="menu-link">
				<i class="menu-icon tf-icons bx bx-home-circle"></i>
				<div data-i18n="Dashboard">Cash Reort</div>
			</a>
		</li>

		{{--  <li class="menu-item {{ request()->is('admin/firm-ledger-report') ? 'active' : ''}}">
			<a href="{{route('admin.firm.ledger.report')}}" class="menu-link">
				<i class="menu-icon tf-icons bx bx-home-circle"></i>
				<div data-i18n="Dashboard">Firm Ledger Report</div>
			</a>
		</li>  --}}

		<li class="menu-item {{ request()->is('admin/firm-ledger-details-report') ? 'active' : ''}}">
			<a href="{{route('admin.firm.ledger.details.report')}}" class="menu-link">
				<i class="menu-icon tf-icons bx bx-home-circle"></i>
				<div data-i18n="Dashboard">Firm Ledger Details</div>
			</a>
		</li>
	</ul>
</aside>
