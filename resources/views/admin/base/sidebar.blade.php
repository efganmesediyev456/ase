<link href="{{ asset('admin/css/base.css') }}?v=1.0.6" rel="stylesheet">
<ul class="navigation navigation-main navigation-accordion">

    <!-- Main -->
    <li class="navigation-header"><span>Main</span> <i class="icon-menu" title="Main pages"></i></li>
    @permission('read-dashboard')
    <li {!! classActiveRoute('dashboard') !!}><a href="{{ route('dashboard') }}"><i class="icon-home4"></i> <span>Dashboard</span></a>
    </li>
    @endpermission
    @permission('read-cells')
    <li {!! classActiveRoute('cells.index') !!}><a href="{{ route('cells.index') }}"><i class="icon-list-numbered"></i>
            <span>Packages/Cells</span></a></li>
    <li {!! classActiveRoute('cells.index') !!}><a href="{{ route('cells.index') }}?requested=1"><i
                    class="icon-question3"></i> <span>Waiting</span>
            @if(isset($_packages['requested']))
                <span class="badge badge-danger align-self-center ml-auto">{{ $_packages['requested'] }}</span>
            @endif
        </a></li>
    @endpermission
    @permission('read-warehouses')
    <li>
        <a href="#"><i class="icon-office"></i> <span>Warehouse</span></a>
        <ul>
            @permission('read-warehouses')
            <li {!! classActiveRoute('warehouses.index') !!}><a href="{{ route('warehouses.index') }}"><i
                            class="icon-office"></i> <span>Warehouses</span></a></li>
            @endpermission

            @permission('read-cities')
            <li {!! classActiveRoute('cities.index') !!}><a href="{{ route('cities.index') }}"><i
                            class="icon-location3"></i> <span>Cities</span></a></li>
            @endpermission

            @permission('read-countries')
            <li {!! classActiveRoute('countries.index') !!}><a href="{{ route('countries.index') }}"><i
                            class="icon-location4"></i> <span>Countries</span></a></li>
            @endpermission

            @permission('read-tariffs')
            <li {!! classActiveRoute('tariffs.index') !!}><a href="{{ route('tariffs.index') }}"><i
                            class="icon-coin-dollar"></i> <span>Tariffs</span></a></li>
            @endpermission

            @permission('read-weight_prices')
            <li {!! classActiveRoute('weight_prices.index') !!}><a href="{{ route('weight_prices.index') }}"><i
                            class="icon-coin-dollar"></i> <span>Weight Prices</span></a></li>
            @endpermission
            @permission('read-delivery_points')
            <li {!! classActiveRoute('delivery_points.index') !!}><a href="{{ route('delivery_points.index') }}"><i
                            class="icon-location4"></i> <span>Delivery Points</span></a></li>
            @endpermission
        </ul>
    </li>
    @endpermission

    @permission('read-packages')
    <li>
        <a href="#"><i class="icon-package"></i> <span>Package</span></a>
        <ul>
            @permission('read-packages')
            <li {!! classActiveRoute('packages.index') !!}>
                <a href="{{ route('packages.index') }}">
                    <i class="icon-package"></i> <span>Packages</span>
                    @if(isset($_packages['active']))
                        <span class="badge badge-info align-self-center ml-auto">{{ $_packages['active'] }}</span>
                    @endif
                </a>
            </li>
            @endpermission

            @permission('read-paids')
            <li {!! classActiveRoute('paids.index') !!}><a href="{{ route('paids.index') }}"><i class="icon-cart"></i>
                    <span>Process paid</span>
                    @if(isset($_packages['paid']))
                        <span class="badge badge-warning align-self-center ml-auto">{{ $_packages['paid'] }}</span>
                    @endif
                </a>
            </li>
            @endpermission

            @permission('read-unknowns')
            <li {!! classActiveRoute('unknowns.index') !!}><a href="{{ route('unknowns.index') }}"><i
                            class="icon-close2"></i> <span>Unknowns</span>
                    @if(isset($_packages['unknown']))
                        <span class="badge badge-danger align-self-center ml-auto">{{ $_packages['unknown'] }}</span>
                    @endif
                </a>
            </li>
            @endpermission

            @permission('read-dones')
            <li {!! classActiveRoute('dones.index') !!}><a href="{{ route('dones.index') }}"><i
                            class="icon-spinner"></i> <span>Completed</span>
                    @if(isset($_packages['done']))
                        <span class="badge badge-success align-self-center ml-auto">{{ $_packages['done'] }}</span>
                    @endif
                </a></li>
            @endpermission

            @permission('read-parcels')
            <li {!! classActiveRoute('parcels.index') !!}><a href="{{ route('parcels.index') }}"><i
                            class="icon-barcode2"></i> <span>Parcels</span></a></li>
            @endpermission

            @permission('read-logic_sync')
            <li {!! classActiveRoute('logic_syncs.index') !!}><a href="{{ route('logic_syncs.index') }}"><i
                            class="icon-loop"></i> <span>Logic Sync</span></a></li>
            @endpermission


            @permission('read-package_types')
            <li {!! classActiveRoute('package_types.index') !!}><a href="{{ route('package_types.index') }}"><i
                            class="icon-list"></i> <span>Types</span></a></li>
            @endpermission

            @permission('read-newtypes')
            <li {!! classActiveRoute('newtypes.index') !!}><a href="{{ route('newtypes.index') }}"><i
                            class="icon-list"></i> <span>New Types</span></a></li>
            @endpermission

            @permission('read-transactions')
            <li {!! classActiveRoute('transactions.index') !!}><a href="{{ route('transactions.index') }}"><i
                            class="icon-basket"></i> <span>Transactions</span></a></li>
            @endpermission
        </ul>
    </li>
    @endpermission
    @permission('read-tracks')
    <li>
        <a href="#"><i class="icon-package"></i> <span>Tracks</span></a>
        <ul>
            @permission('read-tracks')
            <li {!! classActiveRoute('tracks.index') !!}>
                <a href="{{ route('tracks.index') }}">
                    <i class="icon-package"></i> <span>Tracks</span>
                </a>
            </li>
            @endpermission
            @permission('read-tracks')
            <li {!! classActiveRoute('in_customs_tracks.index') !!}>
                <a href="{{ route('in_customs_tracks.index') }}">
                    <i class="icon-cart"></i> <span>Check Customs Tracks</span>
                </a>
            </li>
            @endpermission
            @permission('read-packages')
            <li {!! classActiveRoute('in_customs_packages.set') !!}>
                <a href="{{ route('in_customs_packages.set') }}">
                    <i class="icon-cart"></i> <span>Check Customs Packages</span>
                </a>
            </li>
            @endpermission
            @permission('read-tracks')
            <li {!! classActiveRoute('status_tracks.index') !!}>
                <a href="{{ route('status_tracks.index') }}">
                    <i class="icon-spinner"></i> <span>Status Tracks</span>
                </a>
            </li>
            @endpermission
            @permission('create-tracks')
            <li {!! classActiveRoute('tracks_import_ihb.index') !!}>
                <a href="{{ route('tracks_import_ihb.index') }}">
                    <i class="icon-loop"></i> <span>IHB Import</span>
                </a>
            </li>
{{--            <li {!! classActiveRoute('tracks_import_wb.index') !!}>
                <a href="{{ route('tracks_import_wb.index') }}">
                    <i class="icon-loop"></i> <span>WB Import</span>
                </a>
            </li>
            <li {!! classActiveRoute('tracks_import_ozn.index') !!}>
                <a href="{{ route('tracks_import_ozn.index') }}">
                    <i class="icon-loop"></i> <span>OZN Import</span>
                </a>
            </li> --}}
            <li {!! classActiveRoute('tracks_import_tmuaz.index') !!}>
                <a href="{{ route('tracks_import_tmuaz.index') }}">
                    <i class="icon-loop"></i> <span>Makeup Import</span>
                </a>
            </li>
            <li {!! classActiveRoute('tracks_import_cseru.index') !!}>
                <a href="{{ route('tracks_import_cseru.index') }}">
                    <i class="icon-loop"></i> <span>CSE RU Import</span>
                </a>
            </li>
            <li {!! classActiveRoute('tracks_import_aseexpresstr.index') !!}>
                <a href="{{ route('tracks_import_aseexpresstr.index') }}">
                    <i class="icon-loop"></i> <span>Ase Express TR Import</span>
                </a>
            </li>
            <li {!! classActiveRoute('tracks_import_china.index') !!}>
                <a href="{{ route('tracks_import_china.index') }}">
                    <i class="icon-loop"></i> <span>China Meest Import</span>
                </a>
            </li>
            @endpermission
            @permission('read-containers')
            <li {!! classActiveRoute('containers.index') !!}>
                <a href="{{ route('containers.index') }}">
                    <i class="icon-barcode2"></i> <span>Parcels</span>
                </a>
            </li>
            @endpermission
            @permission('read-customers')
            <li {!! classActiveRoute('customers.index') !!}>
                <a href="{{ route('customers.index') }}">
                    <i class="icon-users"></i> <span>Customers</span>
                </a>
            </li>
            @endpermission
        </ul>
    </li>
    @endpermission

    @permission('read-ue_tickets')
    <li>
        <a href="#"><i class="icon-cart"></i> <span>UE</span></a>
        <ul>
            <li {!! classActiveRoute('ue_tickets.index') !!}><a href="{{ route('ue_tickets.index') }}"><i
                            class="icon-camera"></i> <span>UE Support Tickets</span></a></li>
            <li {!! classActiveRoute('ue_checkups.index') !!}><a href="{{ route('ue_checkups.index') }}"><i
                            class="icon-camera"></i> <span>UE Checkups</span></a></li>
        </ul>
    </li>
    @endpermission

    @permission('read-package_carriers')
    <li>
        <a href="#"><i class="icon-cart"></i> <span>Customs</span></a>
        <ul>
            <li {!! classActiveRoute('package_carriers.index') !!}><a href="{{ route('package_carriers.index') }}"><i
                            class="icon-package"></i> <span>Packages</span></a></li>
            <li {!! classActiveRoute('air_packages.index') !!}><a href="{{ route('air_packages.index') }}"><i
                            class="icon-cart"></i> <span>AirwayBill</span></a></li>
            <li {!! classActiveRoute('bulk_customs.index') !!}><a href="{{ route('bulk_customs.index') }}"><i class="icon-cart"></i> <span>Bulk customs</span></a></li>
        </ul>
    </li>
    @endpermission

    @permission('read-discounts')
    <li>
        <a href="#"><i class="icon-heart5"></i> <span>Loyality</span></a>
        <ul>
            @permission('read-discounts')
            <li {!! classActiveRoute('discounts.index') !!}><a href="{{ route('discounts.index') }}"><i
                            class="icon-pie-chart"></i> <span>Discounts</span></a></li>
            @endpermission
            @permission('read-promos')
            <li {!! classActiveRoute('promos.index') !!}><a href="{{ route('promos.index') }}"><i
                            class="icon-trophy2"></i> <span>Promo codes</span></a></li>
            @endpermission
            @permission('read-promo_logs')
            <li {!! classActiveRoute('promo_logs.index') !!}><a href="{{ route('promo_logs.index') }}"><i
                            class="icon-list"></i> <span>Promo logs</span></a></li>
            @endpermission
        </ul>
    </li>
    @endpermission

    @permission('read-debt_page')
    <li>
        <a href="#"><i class="icon-office"></i> <span>Saxlanc</span>
        </a>
        <ul>
            @permission('read-debt_package')
            <li {!! classActiveRoute('debt.package.index') !!}><a href="{{ route('debt.package.index') }}"><i
                            class="icon-package"></i> <span>USA/UK/TR/GER</span></a></li>
            @endpermission
            @permission('read-debt_track')
            <li {!! classActiveRoute('debt.track.index') !!}><a
                        href="{{ route('debt.track.index') }}"><i class="icon-package"></i> <span>OZON/IHERB/TAOBAO</span>
                </a></li>
            @endpermission
        </ul>
    </li>
    @endpermission

    @permission('read-courier_deliveries')
    <li>
        <a href="#"><i class="icon-truck"></i> <span>Courier Delivery</span>
            @if(isset($_cds['new']))
                <span class="badge badge-danger align-self-center ml-auto">{{ $_cds['new'] }}</span>
            @endif
        </a>
        <ul>
            @permission('read-couriers')
            <li {!! classActiveRoute('couriers.index') !!}><a href="{{ route('couriers.index') }}"><i
                            class="icon-users"></i> <span>Couriers</span></a></li>
            @endpermission
            @permission('read-courier_deliveries')
            <li {!! classActiveRoute('courier_deliveries.index') !!}><a
                        href="{{ route('courier_deliveries.index') }}"><i class="icon-truck"></i> <span>Courier Deliveries</span>
                    @if(isset($_cds['new']))
                        <span class="badge badge-danger align-self-center ml-auto">{{ $_cds['new'] }}</span>
                    @endif
                </a></li>
            @endpermission
            @permission('read-courier_deliveries')
            <li {!! classActiveRoute('courier.shelf.index') !!}><a
                        href="{{ route('courier.shelf.index') }}"><i class="icon-list"></i> <span>Courier Shelf</span>
                </a></li>
            @endpermission
            @permission('read-courier_areas')
            <li {!! classActiveRoute('courier_areas.index') !!}><a
                    href="{{ route('courier_areas.index') }}"><i class="icon-map"></i> <span>Courier Areas</span>
                </a></li>
            @endpermission
            @permission('read-courier_tracks')
            <li {!! classActiveRoute('courier_tracks.index') !!}><a
                    href="{{ route('courier_tracks.index') }}"><i class="icon-barcode2"></i> <span>Courier Track Scan</span>
                </a></li>
            @endpermission

            <li {!! classActiveRoute('points.create') !!}><a
                        href="{{ route('points.create') }}"><i class="icon-barcode2"></i> <span>Import map</span>
                </a></li>
        </ul>
    </li>
    @endpermission

    @permission('read-filials')
    <li {!! classActiveRoute('filials.index') !!}><a href="{{ route('filials.index') }}"><i class="icon-location4"></i>
            <span>Filials</span></a></li>
    @endpermission

    @permission('read-boxes')
    <li>
        <a href="#"><i class="icon-location3"></i> <span>Qutular</span></a>
        <ul>
            <li {!! classActiveRoute('hub.packages') !!}>
                <a href="{{ route('hub.packages') }}"><i class="icon-list-ordered"></i>
                    <span>Dashboard</span></a>
            </li>
            <li {!! classActiveRoute('hub.boxes') !!}>
                <a href="{{ route('hub.boxes') }}"><i class="icon-list-ordered"></i>
                    <span>Qutular</span></a>
            </li>
        </ul>
    </li>
    @endpermission

    @permission('read-azeriexpress')
    <li>
        <a href="#"><i class="icon-location3"></i> <span>AzeriExpress</span></a>
        <ul>
            @permission('read-azeriexpress_packages')
            <li {!! classActiveRoute('azeriexpress.index') !!}>
                <a href="{{ route('azeriexpress.index') }}"><i class="icon-list-ordered"></i>
                    <span>Bağlamalar</span></a>
            </li>
            @endpermission

            @permission('read-azeriexpress_containers')
            <li {!! classActiveRoute('azeriexpress.containers') !!}>
                <a href="{{ route('azeriexpress.containers') }}"><i class="icon-truck"></i> <span>Konteynerler</span>
                </a>
            </li>
            @endpermission
            <li {!! classActiveRoute('azeriexpress.courier-containers') !!}>
                <a href="{{ route('azeriexpress.courier-containers') }}"><i class="icon-truck"></i> <span>Kuryer Konteynerler</span>
                </a>
            </li>

            @permission('read-azeriexpress_offices')
            <li {!! classActiveRoute('azeriexpress.offices') !!}>
                <a href="{{ route('azeriexpress.offices') }}"><i class="icon-location4"></i> <span>Ofislər</span>
                </a>
            </li>
            @endpermission
            @permission('read-azeriexpress_done_containers')
            <li {!! classActiveRoute('azeriexpress.containers') !!}>
                <a href="{{ route('azeriexpress.containers', ['status' => "done"]) }}"><i class="icon-location4"></i>
                    <span>Done Konteynerler</span>
                </a>
            </li>
            @endpermission
        </ul>
    </li>
    @endpermission

    @permission('read-azerpost')
    <li>
        <a href="#"><i class="icon-location3"></i> <span>Azerpost</span></a>
        <ul>
            @permission('read-azerpost_packages')
            <li {!! classActiveRoute('azerpost.index') !!}>
                <a href="{{ route('azerpost.index') }}"><i class="icon-list-ordered"></i>
                    <span>Bağlamalar</span></a>
            </li>
            @endpermission
            @permission('read-azerpost_containers')
            <li {!! classActiveRoute('azerpost.containers') !!}>
                <a href="{{ route('azerpost.containers') }}"><i class="icon-truck"></i> <span>Konteynerler</span>
                </a>
            </li>
            @endpermission

            @permission('read-azerpost_offices')
            <li {!! classActiveRoute('azerpost.offices') !!}>
                <a href="{{ route('azerpost.offices') }}"><i class="icon-location4"></i> <span>Ofislər</span>
                </a>
            </li>
            @endpermission
            @permission('read-azerpost_done_containers')
            <li {!! classActiveRoute('azerpost.containers') !!}>
                <a href="{{ route('azerpost.containers', ['status' => "done"]) }}"><i class="icon-location4"></i>
                    <span>Done Konteynerler</span>
                </a>
            </li>
            @endpermission
        </ul>
    </li>
    @endpermission

    @permission('read-yenipoct')
    <li>
        <a href="#"><i class="icon-location3"></i> <span>YeniPoct</span></a>
        <ul>
            @permission('read-yenipoct_packages')
            <li {!! classActiveRoute('yenipoct.index') !!}>
                <a href="{{ route('yenipoct.index') }}"><i class="icon-list-ordered"></i>
                    <span>Bağlamalar</span></a>
            </li>
            @endpermission

            @permission('read-yenipoct_containers')
            <li {!! classActiveRoute('yenipoct.containers') !!}>
                <a href="{{ route('yenipoct.containers') }}"><i class="icon-truck"></i> <span>Konteynerler</span>
                </a>
            </li>
            @endpermission

            @permission('read-yenipoct_offices')
            <li {!! classActiveRoute('yenipoct.offices') !!}>
                <a href="{{ route('yenipoct.offices') }}"><i class="icon-location4"></i> <span>Ofislər</span>
                </a>
            </li>
            @endpermission
            @permission('read-yenipoct_done_containers')
            <li {!! classActiveRoute('yenipoct.containers') !!}>
                <a href="{{ route('yenipoct.containers', ['status' => "done"]) }}"><i class="icon-location4"></i>
                    <span>Done Konteynerler</span>
                </a>
            </li>
            @endpermission
        </ul>
    </li>
    @endpermission

    @permission('read-kargomat')
    <li>
        <a href="#"><i class="icon-location3"></i> <span>Kargomat</span></a>
        <ul>
            @permission('read-kargomat_packages')
            <li {!! classActiveRoute('kargomat.index') !!}>
                <a href="{{ route('kargomat.index') }}"><i class="icon-list-ordered"></i>
                    <span>Bağlamalar</span></a>
            </li>
            @endpermission

            @permission('read-kargomat_containers')
            <li {!! classActiveRoute('kargomat.containers') !!}>
                <a href="{{ route('kargomat.containers') }}"><i class="icon-truck"></i> <span>Konteynerler</span>
                </a>
            </li>
            @endpermission

            @permission('read-kargomat_offices')
            <li {!! classActiveRoute('kargomat.offices') !!}>
                <a href="{{ route('kargomat.offices') }}"><i class="icon-location4"></i> <span>Ofislər</span>
                </a>
            </li>
            @endpermission
            @permission('read-kargomat_done_containers')
            <li {!! classActiveRoute('kargomat.containers') !!}>
                <a href="{{ route('kargomat.containers', ['status' => "done"]) }}"><i class="icon-location4"></i>
                    <span>Done Konteynerler</span>
                </a>
            </li>
            @endpermission
        </ul>
    </li>
    @endpermission

    @permission('read-precinct')
    <li>
        <a href="#"><i class="icon-location3"></i> <span>Precinct</span></a>
        <ul>
            @permission('read-precinct_packages')
            <li {!! classActiveRoute('precinct.index') !!}>
                <a href="{{ route('precinct.index') }}"><i class="icon-list-ordered"></i> <span>Bağlamalar</span></a>
            </li>
            @endpermission
            @permission('read-precinct_containers')
            <li {!! classActiveRoute('precinct.containers') !!}>
                <a href="{{ route('precinct.containers') }}"><i class="icon-box"></i> <span>Konteynerler</span>
                </a>
            </li>
            @endpermission
            @permission('read-precinct_packages')
            {{--            <li {!! classActiveRoute('precinct.not-send-packages') !!}>--}}
            {{--                <a href="{{ route('precinct.not-send-packages') }}"><i class="icon-truck"></i> <span>Göndərilməyən bağlamalar</span>--}}
            {{--                </a>--}}
            {{--            </li>--}}
            @endpermission
            @permission('read-precinct_offices')
            <li {!! classActiveRoute('precinct.offices') !!}>
                <a href="{{ route('precinct.offices') }}"><i class="icon-location4"></i> <span>Ofislər</span>
                </a>
            </li>
            @endpermission
        </ul>
    </li>
    @endpermission

    @permission('read-surat')
    <li>
        <a href="#"><i class="icon-location3"></i> <span>Surat Kargo</span></a>
        <ul>
            @permission('read-surat_packages')
            <li {!! classActiveRoute('surat.index') !!}>
                <a href="{{ route('surat.index') }}"><i class="icon-list-ordered"></i>
                    <span>Bağlamalar</span></a>
            </li>
            @endpermission

            @permission('read-surat_containers')
            <li {!! classActiveRoute('surat.containers') !!}>
                <a href="{{ route('surat.containers') }}"><i class="icon-truck"></i> <span>Konteynerler</span>
                </a>
            </li>
            @endpermission

            @permission('read-surat_offices')
            <li {!! classActiveRoute('surat.offices') !!}>
                <a href="{{ route('surat.offices') }}"><i class="icon-location4"></i> <span>Ofislər</span>
                </a>
            </li>
            @endpermission
            @permission('read-surat_done_containers')
            <li {!! classActiveRoute('surat.containers') !!}>
                <a href="{{ route('surat.containers', ['status' => "done"]) }}"><i class="icon-location4"></i>
                    <span>Done Konteynerler</span>
                </a>
            </li>
            @endpermission
        </ul>
    </li>
    @endpermission

    @permission('read-courier-saas')
    <li>
        <a href="#"><i class="icon-location3"></i> <span>SAAS Kuryer</span></a>
        <ul>
            @permission('read-courier-saas_packages')
            <li {!! classActiveRoute('courier-saas.index') !!}>
                <a href="{{ route('courier-saas.index') }}"><i class="icon-list-ordered"></i>
                    <span>Bağlamalar</span></a>
            </li>
            @endpermission

            @permission('read-courier-saas_containers')
            <li {!! classActiveRoute('courier-saas.containers') !!}>
                <a href="{{ route('courier-saas.containers') }}"><i class="icon-truck"></i> <span>Konteynerler</span>
                </a>
            </li>
            @endpermission

            @permission('read-courier-saas_done_containers')
            <li {!! classActiveRoute('courier-saas.containers') !!}>
                <a href="{{ route('courier-saas.containers', ['status' => "done"]) }}"><i class="icon-location4"></i>
                    <span>Done Konteynerler</span>
                </a>
            </li>
            @endpermission
        </ul>
    </li>
    @endpermission

    @permission('read-stores')
    <li>
        <a href="#"><i class="icon-price-tags"></i> <span>Store</span></a>
        <ul>
            @permission('read-stores')
            <li {!! classActiveRoute('stores.index') !!}><a href="{{ route('stores.index') }}"><i
                            class="icon-apple2"></i> <span>Stores</span></a></li>
            @endpermission

            @permission('read-coupons')
            <li {!! classActiveRoute('coupons.index') !!}><a href="{{ route('coupons.index') }}"><i
                            class="icon-qrcode"></i> <span>Coupons</span></a></li>
            @endpermission

            @permission('read-products')
            <li {!! classActiveRoute('products.index') !!}><a href="{{ route('products.index') }}"><i
                            class="icon-display4"></i> <span>Products</span></a></li>
            @endpermission

            @permission('read-categories')
            <li {!! classActiveRoute('categories.index') !!}><a href="{{ route('categories.index') }}"><i
                            class="icon-bag"></i> <span>Categories</span></a></li>
            @endpermission
        </ul>
    </li>
    @endpermission

    @permission('read-orders')
    <li {!! classActiveRoute('orders.index') !!}><a href="{{ route('orders.index') }}"><i class="icon-unlink"></i>
            <span>Requests</span></a></li>
    @endpermission

    @permission('read-users')
    <li {!! classActiveRoute('users.index') !!}><a href="{{ route('users.index') }}"><i class="icon-users"></i> <span>Users</span></a>
    </li>
    @endpermission

    @permission('read-campaigns')
    <li {!! classActiveRoute('campaigns.index') !!}><a href="{{ route('campaigns.index') }}"><i class="icon-alarm"></i>
            <span>Campaigns</span></a></li>
    @endpermission

    @permission('read-news')
    <li {!! classActiveRoute('news.index') !!}><a href="{{ route('news.index') }}"><i class="icon-newspaper"></i> <span>News</span></a>
    </li>
    @endpermission

    @permission('read-pages')
    <li>
        <a href="#"><i class="icon-file-empty"></i> <span>Page</span></a>
        <ul>
            @permission('read-pages')
            <li {!! classActiveRoute('pages.index') !!}><a href="{{ route('pages.index') }}"><i
                            class="icon-files-empty"></i> <span>Pages</span></a></li>
            @endpermission

            @permission('read-faqs')
            <li {!! classActiveRoute('faqs.index') !!}><a href="{{ route('faqs.index') }}"><i class="icon-qrcode"></i>
                    <span>FAQ</span></a></li>
            @endpermission

            @permission('read-contacts')
            <li {!! classActiveRoute('contacts.index') !!}><a href="{{ route('contacts.index') }}"><i class="icon-envelope"></i>
                    <span>Contact Messages</span></a></li>
            @endpermission
        </ul>
    </li>
    @endpermission

    @permission('read-sliders')
    <li {!! classActiveRoute('sliders.index') !!}><a href="{{ route('sliders.index') }}"><i class="icon-images3"></i>
            <span>Slider</span></a></li>
    @endpermission

    @permission('read-sms')
    <li {!! classActiveRoute('sms.index') !!}><a href="{{ route('sms.index') }}"><i class="icon-mobile"></i> <span>SMS Templates</span></a>
    </li>
    @endpermission

    @permission('read-whatsapps')
    <li {!! classActiveRoute('whatsapps.index') !!}><a href="{{ route('whatsapps.index') }}"><i class="icon-mobile"></i>
            <span>Whatsapp Templates</span></a></li>
    @endpermission

    @permission('read-mobiles')
    <li {!! classActiveRoute('mobiles.index') !!}><a href="{{ route('mobiles.index') }}"><i class="icon-mobile"></i>
            <span>Mobile Templates</span></a></li>
    @endpermission

    @permission('read-emails')
    <li {!! classActiveRoute('emails.index') !!}><a href="{{ route('emails.index') }}"><i class="icon-mail-read"></i>
            <span>Email Templates</span></a></li>
    @endpermission

    @permission('read-notifications')
    <li {!! classActiveRoute('notifications.index') !!}>
        <a href="{{ route('notifications.index') }}">
            <i class="icon-mail-read"></i> Notifications
        </a>
    </li>
    @endpermission

    @permission('read-gift_cards')
    <li {!! classActiveRoute('gift_card.index') !!}><a href="{{ route('gift_cards.index') }}"><i class="icon-gift"></i>
            <span>Gift Cards</span></a></li>
    @endpermission

    @permission('read-admins')
    <li>
        <a href="#"><i class="icon-user-tie"></i> <span>Admins</span></a>
        <ul>
            <li {!! classActiveRoute('admins.index') !!}><a href="{{ route('admins.index') }}"><i
                            class="icon-user-tie"></i> <span>Admins</span></a></li>
            @permission('read-roles')
            <li {!! classActiveRoute('roles.index') !!}><a href="{{ route('roles.index') }}"><i class="icon-qrcode"></i>
                    <span>Roles</span></a></li>
            @endpermission
        </ul>
    </li>
    @endpermission

    @permission('read-settings')
    <li {!! classActiveRoute('settings.index') !!}><a href="{{ route('settings.edit', 1) }}"><i class="icon-gear"></i>
            <span>Settings</span></a></li>
    @endpermission

    @permission('read-activities')
    <li {!! classActiveRoute('activities.index') !!}><a href="{{ route('activities.index') }}"><i class="icon-list"></i>
            <span>Activities</span></a></li>
    @endpermission

    @permission('read-translations')
    <li {!! classActiveRoute('settings.index') !!}><a href="{{ url('translations') }}"><i class="icon-code"></i> <span>Translations</span></a>
    </li>
    @endpermission
    @permission('read-export_delivery_date')
    <li {!! classActiveRoute('export_delivery_date.index') !!}><a href="{{ route('export_delivery_date.index') }}"><i class="icon-code"></i> <span>Export Delivery Dates</span></a>
    </li>
    @endpermission
    @permission('read-bulk_resend_statuses')
    <li {!! classActiveRoute('bulk_resend_statuses.index') !!}><a href="{{ route('bulk_resend_statuses.index') }}"><i class="icon-code"></i> <span>Bulk resend status</span></a>
    @endpermission
    @permission('read-instagrams')
    <li {!! classActiveRoute('instagrams.index') !!}><a href="{{ route('instagrams.index') }}"><i class="icon-code"></i> <span>Bizi instagramdan izləyin</span></a>
    </li>
    @endpermission
    @permission('read-careers')
    <li {!! classActiveRoute('careers.index') !!}><a href="{{ route('careers.index') }}"><i class="icon-code"></i> <span>Vakansiyalar</span></a>
    </li>
    @endpermission
    @permission('read-applications')
    <li {!! classActiveRoute('applications.index') !!}><a href="{{ route('applications.index') }}"><i class="icon-code"></i> <span>Vakansiya müraciətlər</span></a>
    </li>
    @endpermission
</ul>
