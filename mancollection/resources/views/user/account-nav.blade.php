<style>
    .active-menu {
        color: #008ae6;
        font-wergb(255, 78, 24)old;
        padding-left: 1rem;
        /* Adjust as needed */
        padding-right: 1rem;
        /* Adjust as needed */
    }
</style>
<div class="col-lg-3">
    <ul class="account-nav">
        <li><a href="{{ route('user.index') }}" class="menu-link menu-link_us-s" id="dashboard-link">Dashboard</a></li>
        <li><a href="{{ route('user.orders') }}" class="menu-link menu-link_us-s" id="orders-link">Orders</a></li>
        <li><a href="account-address.html" class="menu-link menu-link_us-s" id="address-link">Addresses</a></li>
        <li><a href="account-details.html" class="menu-link menu-link_us-s" id="details-link">Account Details</a></li>
        <li><a href="account-wishlist.html" class="menu-link menu-link_us-s" id="wishlist-link">Wishlist</a></li>
        <li>
            <form method="POST" action="{{ route('logout') }}" id="form-logout">
                @csrf
                <a href="{{ route('logout') }}" class="menu-link menu-link_us-s"
                    onclick="event.preventDefault(); document.getElementById('form-logout').submit();">Logout</a>
            </form>
        </li>
    </ul>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the current URL path
        let path = window.location.pathname;

        // Define an object to map paths to the corresponding link IDs
        const linkMap = {
            '/user/dashboard': 'dashboard-link',
            '/user/orders': 'orders-link',
            '/account-address.html': 'address-link',
            '/account-details.html': 'details-link',
            '/account-wishlist.html': 'wishlist-link',
        };

        // Get the link ID based on the current path
        let activeLinkId = linkMap[path];

        // If there's a matching link, add the active class
        if (activeLinkId) {
            document.getElementById(activeLinkId).classList.add('active-menu');
        }
    });
</script>
