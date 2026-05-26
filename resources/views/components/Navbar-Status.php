{{-- ═══════════════════════════════════════════════════════════════════════════
     NAVBAR STATUS INDICATOR
     
     Add to your navbar/header: --}}
     
     <li class="nav-item dropdown">
         <a class="nav-link dropdown-toggle" href="#" id="statusDropdown" role="button" data-toggle="dropdown">
             <i class="fas fa-circle fa-sm" data-navbar-status></i>
         </a>
         <div class="dropdown-menu dropdown-menu-right">
             <h6 class="dropdown-header">Your Status</h6>
             <a class="dropdown-item" href="#" onclick="OnlineStatus.setStatus('online'); return false;">
                 <i class="fas fa-circle text-success mr-2"></i> Online
             </a>
             <a class="dropdown-item" href="#" onclick="OnlineStatus.setStatus('away'); return false;">
                 <i class="fas fa-circle text-warning mr-2"></i> Away
             </a>
             <a class="dropdown-item" href="#" onclick="OnlineStatus.setStatus('busy'); return false;">
                 <i class="fas fa-circle text-danger mr-2"></i> Busy
             </a>
             <div class="dropdown-divider"></div>
             <a class="dropdown-item" href="#" onclick="OnlineStatus.setStatus('offline'); return false;">
                 <i class="fas fa-circle text-secondary mr-2"></i> Offline
             </a>
         </div>
     </li>