<li class="side-menus {{ Request::is('/') ? 'active' : '' }}">
    <a class="nav-link" href="/">
        <i class=" fas fa-building"></i><span>Dashboard</span>
    </a>
</li>
<li class="side-menus {{ Request::is('clients/*')||Request::is('clients')  ? 'active' : '' }}">
    <a class="nav-link has-dropdown" href="#">
    <i class="fas fa-address-card"></i><span>Clients</span>
    </a>
    <ul class="dropdown-menu">
                <li class="{{ Request::is('clients') ? 'active' : '' }}"><a class="nav-link" href="/clients">List</a></li>
                <li class="{{ Request::is('clients/creditcards') ? 'active' : '' }}"><a class="nav-link" href="/clients/creditcards">Credit Cards</a></li>
                <li class="{{ Request::is('clients/import') ? 'active' : '' }}"><a class="nav-link" href="/clients/import">Import</a></li>
                <li class="{{ Request::is('clients/failed-import') ? 'active' : '' }}"><a class="nav-link" href="/clients/failed-import">Failed Imports</a></li>
              </ul>
</li>
<li class="side-menus {{ Request::is('users/*') ? 'active' : '' }}">
    <a class="nav-link has-dropdown" href="#">
        <i class=" fas fa-user"></i><span>Users</span>
    </a>
    <ul class="dropdown-menu">
                <li class="{{ Request::is('users/list') ? 'active' : '' }}"><a class="nav-link" href="/users/list">List</a></li>
                <li class="{{ Request::is('users/add') ? 'active' : '' }}"><a class="nav-link" href="/users/add">Add User</a></li>
              </ul>
</li>
