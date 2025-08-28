<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 " id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <a class="align-items-center d-flex m-0 navbar-brand text-wrap" href="{{ route('dashboard') }}">
      <img src="../assets/img/logo-ct.png" class="navbar-brand-img h-100" alt="...">
      <span class="ms-3 font-weight-bold">Invits</span>
    </a>
  </div>
  <hr class="horizontal dark mt-0">
  <div class="collapse navbar-collapse  w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link {{ (Request::is('dashboard') ? 'active' : '') }}" href="{{ url('dashboard') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <svg width="12px" height="12px" viewBox="0 0 45 40" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <title>shop </title>
              <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                <g transform="translate(-1716.000000, -439.000000)" fill="#FFFFFF" fill-rule="nonzero">
                  <g transform="translate(1716.000000, 291.000000)">
                    <g transform="translate(0.000000, 148.000000)">
                      <path class="color-background opacity-6" d="M46.7199583,10.7414583 L40.8449583,0.949791667 C40.4909749,0.360605034 39.8540131,0 39.1666667,0 L7.83333333,0 C7.1459869,0 6.50902508,0.360605034 6.15504167,0.949791667 L0.280041667,10.7414583 C0.0969176761,11.0460037 -1.23209662e-05,11.3946378 -1.23209662e-05,11.75 C-0.00758042603,16.0663731 3.48367543,19.5725301 7.80004167,19.5833333 L7.81570833,19.5833333 C9.75003686,19.5882688 11.6168794,18.8726691 13.0522917,17.5760417 C16.0171492,20.2556967 20.5292675,20.2556967 23.494125,17.5760417 C26.4604562,20.2616016 30.9794188,20.2616016 33.94575,17.5760417 C36.2421905,19.6477597 39.5441143,20.1708521 42.3684437,18.9103691 C45.1927731,17.649886 47.0084685,14.8428276 47.0000295,11.75 C47.0000295,11.3946378 46.9030823,11.0460037 46.7199583,10.7414583 Z"></path>
                      <path class="color-background" d="M39.198,22.4912623 C37.3776246,22.4928106 35.5817531,22.0149171 33.951625,21.0951667 L33.92225,21.1107282 C31.1430221,22.6838032 27.9255001,22.9318916 24.9844167,21.7998837 C24.4750389,21.605469 23.9777983,21.3722567 23.4960833,21.1018359 L23.4745417,21.1129513 C20.6961809,22.6871153 17.4786145,22.9344611 14.5386667,21.7998837 C14.029926,21.6054643 13.533337,21.3722507 13.0522917,21.1018359 C11.4250962,22.0190609 9.63246555,22.4947009 7.81570833,22.4912623 C7.16510551,22.4842162 6.51607673,22.4173045 5.875,22.2911849 L5.875,44.7220845 C5.875,45.9498589 6.7517757,46.9451667 7.83333333,46.9451667 L19.5833333,46.9451667 L19.5833333,33.6066734 L27.4166667,33.6066734 L27.4166667,46.9451667 L39.1666667,46.9451667 C40.2482243,46.9451667 41.125,45.9498589 41.125,44.7220845 L41.125,22.2822926 C40.4887822,22.4116582 39.8442868,22.4815492 39.198,22.4912623 Z"></path>
                    </g>
                  </g>
                </g>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li>
      <br />
      <br />

      @can('view-reports')
      <li class="nav-item mt-2">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Administration</h6>
      </li>
      <li class="nav-item pb-2">
        <a class="nav-link {{ (Request::is('user-management') ? 'active' : '') }}" href="{{ url('user-management') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <svg width="12px" height="12px" viewBox="0 0 46 42" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <title>support</title>
              <g id="Basic-Elements" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                <g id="Rounded-Icons" transform="translate(-1717.000000, -291.000000)" fill="#FFFFFF" fill-rule="nonzero">
                  <g id="Icons-with-opacity" transform="translate(1716.000000, 291.000000)">
                    <g id="customer-support" transform="translate(1.000000, 0.000000)">
                      <path class="color-background" d="M45,0 L26,0 C25.447,0 25,0.447 25,1 L25,20 C25,20.379 25.214,20.725 25.553,20.895 C25.694,20.965 25.848,21 26,21 C26.212,21 26.424,20.933 26.6,20.8 L34.333,15 L45,15 C45.553,15 46,14.553 46,14 L46,1 C46,0.447 45.553,0 45,0 Z" id="Path" opacity="0.59858631"></path>
                      <path class="color-foreground" d="M22.883,32.86 C20.761,32.012 17.324,31 13,31 C8.676,31 5.239,32.012 3.116,32.86 C1.224,33.619 0,35.438 0,37.494 L0,41 C0,41.553 0.447,42 1,42 L25,42 C25.553,42 26,41.553 26,41 L26,37.494 C26,35.438 24.776,33.619 22.883,32.86 Z" id="Path"></path>
                      <path class="color-foreground" d="M13,28 C17.432,28 21,22.529 21,18 C21,13.589 17.411,10 13,10 C8.589,10 5,13.589 5,18 C5,22.529 8.568,28 13,28 Z" id="Path"></path>
                    </g>
                  </g>
                </g>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Team Management</span>
        </a>
      </li>
      
      <li class="nav-item pb-2">
        <a class="nav-link {{ (Request::is('reports') ? 'active' : '') }}" href="{{ route('reports.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <svg width="12px" height="12px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <title>reports</title>
              <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                <g transform="translate(-2020.000000, -442.000000)" fill="#FFFFFF" fill-rule="nonzero">
                  <g transform="translate(1716.000000, 291.000000)">
                    <g transform="translate(304.000000, 151.000000)">
                      <path class="color-background opacity-6" d="M31.5,40 L8.5,40 C6.567,40 5,38.433 5,36.5 L5,3.5 C5,1.567 6.567,0 8.5,0 L31.5,0 C33.433,0 35,1.567 35,3.5 L35,36.5 C35,38.433 33.433,40 31.5,40 Z"></path>
                      <path class="color-background" d="M11,4 L29,4 C29.552,4 30,3.552 30,3 C30,2.448 29.552,2 29,2 L11,2 C10.448,2 10,2.448 10,3 C10,3.552 10.448,4 11,4 Z M26,38 L14,38 C13.448,38 13,37.552 13,37 C13,36.448 13.448,36 14,36 L26,36 C26.552,36 27,36.448 27,37 C27,37.552 26.552,38 26,38 Z M29,32 L11,32 C10.448,32 10,31.552 10,31 C10,30.448 10.448,30 11,30 L29,30 C29.552,30 30,30.448 30,31 C30,31.552 29.552,32 29,32 Z M29,26 L11,26 C10.448,26 10,25.552 10,25 C10,24.448 10.448,24 11,24 L29,24 C29.552,24 30,24.448 30,25 C30,25.552 29.552,26 29,26 Z M29,20 L11,20 C10.448,20 10,19.552 10,19 C10,18.448 10.448,18 11,18 L29,18 C29.552,18 30,18.448 30,19 C30,19.552 29.552,20 29,20 Z M29,14 L11,14 C10.448,14 10,13.552 10,13 C10,12.448 10.448,12 11,12 L29,12 C29.552,12 30,12.448 30,13 C30,13.552 29.552,14 29,14 Z"></path>
                    </g>
                  </g>
                </g>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Reports</span>
        </a>
      </li>
      @endcan
      <li class="nav-item mt-2">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Business</h6>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ (Request::is('company-files*') ? 'active' : '') }}" href="{{ url('company-files') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-folder text-dark"></i>
          </div>
          <span class="nav-link-text ms-1">Company Files</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ Request::is('quotes*') ? 'active' : '' }}" href="{{ route('quotes.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <svg width="12px" height="12px" viewBox="0 0 42 42" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <title>box-3d-50</title>
              <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                <g transform="translate(-2319.000000, -291.000000)" fill="#FFFFFF" fill-rule="nonzero">
                  <g transform="translate(1716.000000, 291.000000)">
                    <g transform="translate(603.000000, 0.000000)">
                      <path class="color-background" d="M22.7597136,19.3090182 L38.8987031,11.2395234 C39.3926816,10.9925342 39.592906,10.3918611 39.3459167,9.89788265 C39.249157,9.70436312 39.0922432,9.5474453 38.8987261,9.45068056 L20.2741875,0.1378125 L20.2741875,0.1378125 C19.905375,-0.04725 19.469625,-0.04725 19.0995,0.1378125 L3.1011696,8.13815822 C2.60720568,8.38517662 2.40701679,8.98586148 2.6540352,9.4798254 C2.75080129,9.67332903 2.90771305,9.83023153 3.10122239,9.9269862 L21.8652864,19.3090182 C22.1468139,19.4497819 22.4781861,19.4497819 22.7597136,19.3090182 Z"></path>
                      <path class="color-background opacity-6" d="M23.625,22.429159 L23.625,39.8805372 C23.625,40.4328219 24.0727153,40.8805372 24.625,40.8805372 C24.7802551,40.8805372 24.9333778,40.8443874 25.0722402,40.7749511 L41.2741875,32.673375 L41.2741875,32.673375 C41.719125,32.4515625 42,31.9974375 42,31.5 L42,14.241659 C42,13.6893742 41.5522847,13.241659 41,13.241659 C40.8447549,13.241659 40.6916418,13.2778041 40.5527864,13.3472318 L24.1777864,21.5347318 C23.8390024,21.7041238 23.625,22.0503869 23.625,22.429159 Z"></path>
                      <path class="color-background opacity-6" d="M20.4472136,21.5347318 L1.4472136,12.0347318 C0.953235098,11.7877425 0.352562058,11.9879669 0.105572809,12.4819454 C0.0361450918,12.6208008 6.47121774e-16,12.7739139 0,12.929159 L0,30.1875 L0,30.1875 C0,30.6849375 0.280875,31.1390625 0.7258125,31.3621875 L19.5528096,40.7750766 C20.0467945,41.0220531 20.6474623,40.8218132 20.8944388,40.3278283 C20.963859,40.1889789 21,40.0358742 21,39.8806379 L21,22.429159 C21,22.0503869 20.7859976,21.7041238 20.4472136,21.5347318 Z"></path>
                    </g>
                  </g>
                </g>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Quotes</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ Request::is('product-items*') ? 'active' : '' }}" href="{{ route('product-items.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="ni ni-box-2 text-md opacity-10" style="color: #000;" aria-hidden="true"></i>
          </div>
          <span class="nav-link-text ms-1">Product Items</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link {{ Request::is('suppliers*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-industry text-md opacity-10" style="color: #000;" aria-hidden="true"></i>
          </div>
          <span class="nav-link-text ms-1">Suppliers</span>
        </a>
      </li>

      @if(Auth::user()->isRfqProcessor())
      <li class="nav-link mb-0">
        <a href="{{ route('quotes.create') }}" class="btn btn-primary btn-md active px-5 text-white" role="button" aria-pressed="true">
          Create Quote</a>
      </li>
      @endif
    </ul>
  </div>
  <div class="sidenav-footer mx-3">
    <div class="card card-background shadow-none card-background-mask-secondary" id="sidenavCard">
      <div class="card-body text-start p-2 w-100">
        <div class="user-info">
          <h6 class="text-white font-weight-bold mb-0">{{ Auth::user()->name }}</h6>
          <p class="text-xs text-white mb-2">{{ Auth::user()->email }}</p>
          <div class="docs-info d-flex justify-content-between gap-1">
            <a href="{{ url('user-profile') }}" class="btn btn-white btn-xs py-1 px-2 mb-0 w-100">
              <i class="ni ni-single-02 me-1"></i>Profile
            </a>
            <a href="{{ url('logout') }}" class="btn btn-white btn-xs py-1 px-2 mb-0 w-100">Sign Out</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</aside>