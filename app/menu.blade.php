<div class="sidebar">
    <nav class="sidebar-nav">

        <ul class="nav">
            <li class="nav-item">
                <a href="{{ route('admin.home') }}" class="nav-link">
                    <i class="nav-icon fas fa-tachometer-alt">

                    </i>
                    {{ trans('global.dashboard') }}
                </a>
            </li>
            
            <li class="nav-item nav-dropdown">
                <a class="nav-link  nav-dropdown-toggle">
                    <i class="fas fa-clipboard-list  nav-icon">

                    </i>
                    {{ trans('global.ticket_request') }}
                </a>
                <ul class="nav-dropdown-items">
                    @can('ticket_access')    
                    <li class="nav-item">
                        <a href="{{ route('admin.tickets.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class="nav-icon fas fa-clipboard-list"></i>
                            {{ trans('global.ticket.title') }}
                        </a>
                    </li>
                    @endcan
                    @can('customerrequests_access') 
                    <li class="nav-item">
                        <a href="{{ route('admin.customerrequests.index') }}" class="nav-link {{ request()->is('admin/customerrequests') || request()->is('admin/customerrequests/*') ? 'active' : '' }}">
                            <i class="fas fa-user nav-icon">

                            </i>
                            {{ trans('global.customerrequest.title') }}
                        </a>
                    </li>
                    @endcan
                    @can('ctmrequests_access') 
                    <li class="nav-item">
                        <a href="{{ route('admin.ctmrequests.index') }}" class="nav-link {{ request()->is('admin/ctmrequests') || request()->is('admin/ctmrequests/*') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt nav-icon">

                            </i>
                            {{ trans('global.ctmrequest.title') }}
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>   
            @can('report_access') 
            <li class="nav-item nav-dropdown">
                <a class="nav-link  nav-dropdown-toggle">
                    <i class="fas fa-file  nav-icon">

                    </i>
                    {{ trans('global.report') }}
                </a>
                <ul class="nav-dropdown-items">                       
                    @can('reporthumas_access')    
                    <li class="nav-item">
                        <a href="{{ route('admin.report.subhumas') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class="nav-icon fas fa-file"></i>
                        {{ trans('global.reporthumas') }}
                        </a>
                    </li>
                    @endcan
                    @can('reportdistribusi_access')
                    <li class="nav-item">
                        <a href="{{ route('admin.report.subdistribusi') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class="nav-icon fas fa-file"></i>
                        {{ trans('global.reportdistribusi') }}
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcan
            @can('lock_access')
            <li class="nav-item nav-dropdown">
                <a class="nav-link  nav-dropdown-toggle">
                    <i class="nav-icon fas fa-lock">
        
                    </i>
                    {{-- {{ trans('global.segelmeter.index') }} --}}
                    Segel Meter
                </a>
                <ul class="nav-dropdown-items">
                    <!-- <li class="nav-item">
                        <a href="{{ route('admin.segelmeter.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-lock">
        
                            </i>
                            {{ trans('global.segelmeter.title') }}
                        </a>

                    </li> -->
                    @can('lock_access')
                    <li class="nav-item">
                        <a href="{{ route('admin.segelmeter.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-user-lock"></i>
                            Info Tunggakan
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.locks.index') }}" class="nav-link">
                            <i class="nav-icon fa fa-info-circle"></i>
                            Info Tindakan
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.suratsegel.index') }}" class="nav-link">
                            <i class="nav-icon fa fa-envelope-o"></i>
                            Buat Surat
                        </a>
                    </li>
 
                    <li class="nav-item">
                        <a href="{{ route('admin.report.reportLockAction') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class="nav-icon fas fa-file"></i>
                       Rekap Segel Meter
                        </a>
                    </li>
                    @endcan
                    <!-- <li class="nav-item">
                        <a href="{{ route('admin.spp.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-file "></i>
                            Print SPP
                        </a>
                    </li> -->
                </ul>
            </li>
            @endcan

            {{-- start pergantian WM --}}
                
            {{-- @endif --}}
            @can('Wm_access')
            <li class="nav-item nav-dropdown">
                <a class="nav-link  nav-dropdown-toggle">
                    <i class="nav-icon fas fa-lock">
        
                    </i>
                    {{-- {{ trans('global.segelmeter.index') }} --}}
                    Pergantian WM
                </a>
                <ul class="nav-dropdown-items">
                    <!-- <li class="nav-item">
                        <a href="{{ route('admin.statuswm.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-lock">
        
                            </i>
                            {{ trans('global.statuswm.title') }}
                        </a>

                    </li> -->
                    @can('statusWm_access')
                    <li class="nav-item">
                        <a href="{{ route('admin.statuswm.index') }}" class="nav-link">
                            <i class="nav-icon fa fa-check-square-o"></i>
                            Status WM
                        </a>
                    </li>
                    @endcan

                    @can('proposalWm_access')
                    <li class="nav-item">
                        <a href="{{ route('admin.proposalwm.index') }}" class="nav-link">
                            <i class="nav-icon fa fa-hand-pointer-o"></i>
                            Usulan Pergantian
                        </a>
                    </li>
                    @endcan

                    @can('proposalWm_report')
                    <li class="nav-item">
                        <a href="{{ route('admin.report.reportPWM') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class="nav-icon fas fa-file"></i>
                       Rekap Usulan Wm
                        </a>
                    </li>
                    @endcan
 
                    @can('proposalWm_report')
                    <li class="nav-item">
                        <a href="{{ route('admin.report.reportProposalWm') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class="nav-icon fas fa-file"></i>
                       Rekap Pergantian Wm
                        </a>
                    </li>
                    @endcan                    

                    @can('proposalWm_report')
                    <li class="nav-item">
                        <a href="{{ route('admin.proposalwm.index5Year') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class="nav-icon fas fa-exclamation-triangle"></i>
                       Pergantian Wm (>2x)
                        </a>
                    </li>
                    @endcan
                    <!-- <li class="nav-item">
                        <a href="{{ route('admin.spp.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-file "></i>
                            Print SPP
                        </a>
                    </li> -->
                </ul>
            </li>
            @endcan

            {{-- end pergantian WM --}}

               {{-- start pergantian WM --}}
                
            {{-- @endif --}}
            
            <li class="nav-item nav-dropdown">
                <a class="nav-link  nav-dropdown-toggle">
                    <i class="nav-icon fas fa-book">
        
                    </i>
                    {{-- {{ trans('global.segelmeter.index') }} --}}
                    Absen (Uji)
                </a>
                <ul class="nav-dropdown-items">
              
                    @can('absence_access')
                    <li class="nav-item">
                        <a href="{{ route('admin.absence.index') }}" class="nav-link">
                            <i class="nav-icon fa fa-address-book-o"></i>
                           Absen
                        </a>
                    </li>
                    @endcan

                    {{-- @can('shift_access')
                    <li class="nav-item">
                        <a href="{{ route('admin.shift.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class="nav-icon fas fa-tasks"></i>
                        Shift
                        </a>
                    </li>
                    @endcan --}}


                    <li class="nav-item nav-dropdown">
                        <a class="nav-link  nav-dropdown-toggle">
                            <i class="fas fa-hand-o-up  nav-icon">
        
                            </i>
                            Permohonan
                        </a>
                        <ul class="nav-dropdown-items">                       
                            @can('duty_access')
                            <li class="nav-item">
                                <a href="{{ route('admin.duty.index') }}" class="nav-link">
                                <!-- <i class="nav-icon fas fa-landmark"></i> -->
                                <i class="nav-icon fas fa-car"></i>
                               Dinas
                                </a>
                            </li>
                            @endcan
        
                            @can('extra_access')
                            <li class="nav-item">
                                <a href="{{ route('admin.extra.index') }}" class="nav-link">
                                <!-- <i class="nav-icon fas fa-landmark"></i> -->
                                <i class="nav-icon fas fa-wrench"></i>
                               Lembur
                                </a>
                            </li>
                            @endcan
        
                            @can('excuse_access')
                            <li class="nav-item">
                                <a href="{{ route('admin.excuse.index') }}" class="nav-link">
                                <!-- <i class="nav-icon fas fa-landmark"></i> -->
                                <i class="nav-icon fas fa-motorcycle"></i>
                               Permisi
                                </a>
                            </li>
                            @endcan
        
                            @can('leave_access')
                            <li class="nav-item">
                                <a href="{{ route('admin.leave.index') }}" class="nav-link">
                                    <i class="nav-icon fa fa-plane"></i>
                                    Cuti
                                </a>
                            </li>
                            @endcan
        
                            @can('workpermit_access')
                            <li class="nav-item">
                                <a href="{{ route('admin.workPermit.index') }}" class="nav-link">
                                    <i class="nav-icon fa fa-hand-pointer-o"></i>
                                    Izin
                                </a>
                            </li>
                            @endcan

                            @can('absence_all_access')
                            <li class="nav-item">
                                <a href="{{ route('admin.forget.index') }}" class="nav-link">
                                <!-- <i class="nav-icon fas fa-landmark"></i> -->
                                <i class="nav-icon fas fa-question"></i>
                               Lupa Absen
                                </a>
                            </li>
                            @endcan

                            <li class="nav-item">
                                <a href="{{ route('admin.geolocation_off.index') }}" class="nav-link">
                                <!-- <i class="nav-icon fas fa-landmark"></i> -->
                                <i class="nav-icon fas fa-book"></i>
                              Geofence Off
                                </a>
                            </li>
                        </ul>
                    </li>

              

                    @can('holiday_access')
                    <li class="nav-item">
                        <a href="{{ route('admin.holiday.index') }}" class="nav-link">
                            <i class="nav-icon fa fa-picture-o"></i>
                            Hari Libur
                        </a>
                    </li>
                    @endcan

                       {{-- @can('shift_access') --}}
                       <li class="nav-item">
                        <a href="{{ route('admin.shift_parent.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class="nav-icon fas fa-calendar"></i>
                       Jadwal Shift
                        </a>
                    </li>
                    {{-- @endcan --}}

                        {{-- @can('shift_access') --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.shift_change.index') }}" class="nav-link">
                            <!-- <i class="nav-icon fas fa-landmark"></i> -->
                            <i class="nav-icon fas fa-calendar"></i>
                           Tukar Shift
                            </a>
                        </li>
                        {{-- @endcan --}}
 
                    @can('schedule_access')
                    <li class="nav-item">
                        <a href="{{ route('admin.work_type.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class="nav-icon fas fa-calendar"></i>
                       Jadwal
                        </a>
                    </li>
                    @endcan

                          {{-- @can('absence_report') --}}
                     
                        {{-- @endcan --}}
    
                    

                    {{-- @can('absence_report') --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.absence.reportAbsenceExcelView') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class="nav-icon fas fa-book"></i>
                      Laporan Excel
                        </a>
                    </li>
                    {{-- @endcan --}}

                               {{-- @can('absence_report') --}}
                               <li class="nav-item">
                                <a href="{{ route('admin.absence.reportAbsenceView') }}" class="nav-link">
                                <!-- <i class="nav-icon fas fa-landmark"></i> -->
                                <i class="nav-icon fas fa-book"></i>
                              Laporan
                                </a>
                            </li>
                            {{-- @endcan --}}

                            @can('staff_access')
                            <li class="nav-item">
                                <a href="{{ route('admin.staffSpecials.index') }}" class="nav-link">
                                <!-- <i class="nav-icon fas fa-landmark"></i> -->
                                <i class="nav-icon fas fa-user"></i>
                               Staff Spesial
                                </a>
                            </li>
                            @endcan

                    @can('absence_report')
                    <li class="nav-item">
                        <a href="{{ route('admin.schedule.test') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class="nav-icon fas fa-bar-chart"></i>
                       Statistik
                        </a>
                    </li>
                    @endcan
                    
                    <!-- <li class="nav-item">
                        <a href="{{ route('admin.spp.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-file "></i>
                            Print SPP
                        </a>
                    </li> -->
                </ul>
            </li>
            

            {{-- end pergantian WM --}}

{{-- wa --}}
@can('wablast_access')
 
<li class="nav-item nav-dropdown">
    <a class="nav-link  nav-dropdown-toggle">
        <i class="nav-icon fa fa-commenting-o">

        </i>
        {{-- {{ trans('global.segelmeter.index') }} --}}
        WA Blast
    </a>
    <ul class="nav-dropdown-items">
        <!-- <li class="nav-item">
            <a href="{{ route('admin.segelmeter.index') }}" class="nav-link">
                <i class="nav-icon fas fa-lock">

                </i>
                {{ trans('global.segelmeter.title') }}
            </a>

        </li> -->
        <li class="nav-item">
            <a href="{{ route('admin.categoryWA.index') }}" class="nav-link">
                <i class="nav-icon fa fa-th-list"></i>
                Kategori
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.WaTemplate.index') }}" class="nav-link">
                <i class="nav-icon fa fa-text-width"></i>
                Template
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.wablast.index') }}" class="nav-link">
                <i class="nav-icon fa fa-envelope-o"></i>
                Wa Blast
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.customwa.index') }}" class="nav-link">
                <i class="nav-icon fa fa-envelope-o"></i>
                Wa Blast(excel)
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.historywa.index') }}" class="nav-link">
                <i class="nav-icon fa fa-history"></i>
                History
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.checkphone.index') }}" class="nav-link">
                <i class="nav-icon fa fa-phone"></i>
                Cek Nomor
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.devicewa.index') }}" class="nav-link">
                <i class="nav-icon fa fa-mobile"></i>
                Device WA
            </a>
        </li>
        <!-- <li class="nav-item">
            <a href="{{ route('admin.spp.index') }}" class="nav-link">
                <i class="nav-icon fas fa-file "></i>
                Print SPP
            </a>
        </li> -->
    </ul>
</li>
   
@endcan
{{-- wa end --}}
            @can('customer_access')
            <li class="nav-item">
                <a href="{{ route('admin.file.upload') }}" class="nav-link">
                    <i class="nav-icon fas fa-money-bill">
                    </i>
                    {{ trans('global.laporan_audited.title') }}
                </a>
            </li>
            @endcan
            <li class="nav-item nav-dropdown">
                <a class="nav-link  nav-dropdown-toggle">
                    <i class="fas fa-database  nav-icon">

                    </i>
                    {{ trans('global.master.title') }}
                </a>
                <ul class="nav-dropdown-items">
                    @can('customer_access')    
                    <li class="nav-item">
                        <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->is('admin/customers') || request()->is('admin/customers/*') ? 'active' : '' }}">
                            <i class="fas fa-user nav-icon">

                            </i>
                            {{ trans('global.customer.title') }}
                        </a>
                    </li>
                    @endcan
                    @can('categories_access')                    
                    <li class="nav-item">
                        <a href="{{ route('admin.categories.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt">

                            </i>
                            {{ trans('global.category.title') }}
                        </a>
                    </li>
                    @endcan
                    @can('dapertement_access')                    
                    <li class="nav-item">
                        <a href="{{ route('admin.dapertements.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class=" nav-icon far fa-building"></i>
                            {{ trans('global.dapertement.title') }}
                        </a>
                    </li>
                    @endcan
                    @can('subdapertement_access')                    
                    <li class="nav-item">
                        <a href="{{ route('admin.subdapertements.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class=" nav-icon far fa-building"></i>
                            {{ trans('global.subdapertement.title') }}
                        </a>
                    </li>
                    @endcan
                    @can('staff_access')                    
                    <li class="nav-item">
                        <a href="{{ route('admin.staffs.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class=" nav-icon fas fa-people-carry"></i>
                            {{ trans('global.staff.title') }}
                        </a>
                    </li>
                    @endcan   
                    @can('pbk_access')                    
                    <li class="nav-item">
                        <a href="{{ route('admin.pbks.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class=" nav-icon fas fa-user-cog"></i>
                            {{ trans('global.pbk.title') }}
                        </a>
                    </li>
                    @endcan  
                    @can('workUnit_access')          
                    <li class="nav-item">
                        <a href="{{ route('admin.workUnit.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class=" nav-icon fas fa-thumb-tack"></i>
                            {{ trans('global.workUnit.title') }}
                        </a>
                    </li>
                    @endcan  

                       {{-- @can('absence_report') --}}
                       <li class="nav-item">
                        <a href="{{ route('admin.job.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class="nav-icon fas fa-book"></i>
                     Pekerjaan
                        </a>
                    </li>
                    {{-- @endcan --}}

                    {{-- @can('workUnit_access')          
                    <li class="nav-item">
                        <a href="{{ route('admin.workUnit.index') }}" class="nav-link">
                        <!-- <i class="nav-icon fas fa-landmark"></i> -->
                        <i class=" nav-icon fas fa-thumb-tack"></i>
                            {{ trans('global.workUnit.title') }}
                        </a>
                    </li>
                    @endcan   --}}
                </ul>
            </li>
            @can('user_management_access') 
            <li class="nav-item nav-dropdown">
                <a class="nav-link  nav-dropdown-toggle">
                    <i class="fas fa-users nav-icon">

                    </i>
                    {{ trans('global.userManagement.title') }}
                </a>
                <ul class="nav-dropdown-items">
                    <li class="nav-item">
                        <a href="{{ route("admin.permissions.index") }}" class="nav-link {{ request()->is('admin/permissions') || request()->is('admin/permissions/*') ? 'active' : '' }}">
                            <i class="fas fa-unlock-alt nav-icon">

                            </i>
                            {{ trans('global.permission.title') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route("admin.roles.index") }}" class="nav-link {{ request()->is('admin/roles') || request()->is('admin/roles/*') ? 'active' : '' }}">
                            <i class="fas fa-briefcase nav-icon">

                            </i>
                            {{ trans('global.role.title') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route("admin.users.index") }}" class="nav-link {{ request()->is('admin/users') || request()->is('admin/users/*') ? 'active' : '' }}">
                            <i class="fas fa-user nav-icon">

                            </i>
                            {{ trans('global.user.title') }}
                        </a>
                    </li>
                </ul>
            </li>
            @endcan
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                    <i class="nav-icon fas fa-sign-out-alt">

                    </i>
                    {{ trans('global.logout') }}
                </a>
            </li>
        </ul>

        <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
            <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
        </div>
        <div class="ps__rail-y" style="top: 0px; height: 869px; right: 0px;">
            <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 415px;"></div>
        </div>
    </nav>
    <button class="sidebar-minimizer brand-minimizer" type="button"></button>
</div>
