<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>@yield('title')</title>
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <meta name="description" content="" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta property="og:title" content="" />
        <meta property="og:type" content="" />
        <meta property="og:url" content="" />
        <meta property="og:image" content="" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/imgs/icons/nidus_icon_2.png')}}" />
        <!-- Template CSS -->
        <link href="{{asset('assets/css/main.css?v=1.1')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/custom-design.css')}}" rel="stylesheet" type="text/css" />
        {{-- yajra css file --}}
        <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf/notyf.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@just-validate/core@3.4.1/dist/just-validate.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <link href="{{asset('assets/css/flat-icons/uicons-thin-rounded.css')}}" rel="stylesheet">
    </head>

    <body>
        @include('shared.navs.sidebar')

        <main class="main-wrap">
           @include('shared.navs.topbar')

            <div style="min-height: 90vh">
                @yield('content')
            </div>

           @include('shared.footer')
        </main>

        @flasher_render

        @if (auth()->check())
            <script>
                window.authUser = @json(auth()->user());
                // You can also store the token if using a session or any custom logic
                localStorage.setItem('auth_token', '{{ csrf_token() }}'); // Just an example; replace with your token logic
            </script>
        @endif




        <script src="{{asset('assets/js/vendors/jquery-3.6.0.min.js')}}"></script>
        <script src="{{asset('assets/js/vendors/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('assets/js/vendors/select2.min.js')}}"></script>
        <script src="{{asset('assets/js/vendors/perfect-scrollbar.js')}}"></script>
        <script src="{{asset('assets/js/vendors/jquery.fullscreen.min.js')}}"></script>
        <script src="{{asset('assets/js/vendors/chart.js')}}"></script>
        <script src="{{asset('assets/js/vendors/parsley.min.js')}}"></script>
        <script src="https://cdn.jsdelivr.net/npm/notyf/notyf.min.js"></script>

        <script>
            var notyf = new Notyf({
                duration: 3000, // Duration of notification in milliseconds
                ripple: true, // Whether to show the ripple effect
                position: { x: 'right', y: 'top' }, // Position of the notifications
                types: [
                    {
                        type: 'success',
                        background: '#4aed70',
                        color: 'white'
                    },
                    {
                        type: 'error',
                        background: '#ff1c33',
                        color: 'white'
                    }
                ]
            });
        </script>



        {{-- yajra library files --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
        {{-- font awesome --}}
        <script src="https://kit.fontawesome.com/18e8b36c2d.js" crossorigin="anonymous"></script>
        <!-- Main Script -->
        <script src="{{asset('assets/js/main.js?v=1.1')}}" type="text/javascript"></script>
        <script src="{{asset('assets/js/custom-chart.js')}}" type="text/javascript"></script>

        <!-- Include Laravel Echo and Pusher -->
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
        <script>
            window.Pusher = Pusher;

            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: 'b6d7a841355e3a19d08c', // Your Pusher App Key
                cluster: 'ap2', // Your Pusher App Cluster
                encrypted: true,
                authEndpoint: '/broadcasting/auth', // Add this for private channels
                auth: {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('auth_token')}`, // Adjust as needed for authentication
                    },
                },
            });

            const id = {{ auth()->id() }};
            window.Echo.private(`user.${id}`)
                .listen('NotificationEvent', (event) => {
                    console.log('New Notification:', event);
                    alert(`New Notification: ${event.notification.message}`);
                });

                document.addEventListener('DOMContentLoaded', function () {
                    function fetchNotifications() {
                    fetch('/get-notificaion')
                        .then(response => response.json())
                        .then(data => {
                            const notificationList = document.getElementById('notificationList');
                            const notificationCount = document.getElementById('notificationCount');

                            notificationList.innerHTML = ''; // Clear existing notifications
                            notificationCount.textContent = data.length; // Update badge count

                            data.forEach(notification => {
                                // Create a toast container
                                const toast = document.createElement('div');
                                toast.classList.add('toast', 'show', 'align-items-center', 'mb-2');
                                toast.setAttribute('role', 'alert');
                                toast.setAttribute('aria-live', 'assertive');
                                toast.setAttribute('aria-atomic', 'true');

                                // Toast header
                                const toastHeader = document.createElement('div');
                                toastHeader.classList.add('toast-header', 'd-flex', 'justify-content-between');

                                  // Title with "New" badge if unread
                                const titleContainer = document.createElement('div');
                                titleContainer.classList.add('d-flex', 'align-items-center');

                                const title = document.createElement('strong');
                                title.classList.add('me-auto');
                                title.textContent = notification.title;

                                 // Check if the notification is unread
                                if (notification.status === 'unread') {
                                    const unreadBadge = document.createElement('span');
                                    unreadBadge.style.padding = '1px';
                                    unreadBadge.classList.add('custom-notification-badge', 'ms-2');
                                    unreadBadge.textContent = 'New';
                                    titleContainer.appendChild(title);
                                    titleContainer.appendChild(unreadBadge);
                                } else {
                                    titleContainer.appendChild(title);
                                }

                                const time = document.createElement('small');
                                time.textContent = timeAgo(new Date(notification.created_at));

                                const closeButton = document.createElement('button');
                                closeButton.classList.add('btn-close', 'btn-close-danger', 'ms-2', 'mb-1');
                                closeButton.setAttribute('data-bs-dismiss', 'toast');
                                closeButton.setAttribute('aria-label', 'Close');

                                toastHeader.appendChild(titleContainer);
                                toastHeader.appendChild(time);
                                toastHeader.appendChild(closeButton);

                                // Toast body
                                const toastBody = document.createElement('div');
                                toastBody.classList.add('toast-body');
                                toastBody.textContent = notification.message;

                                toast.appendChild(toastHeader);
                                toast.appendChild(toastBody);

                                // Add click event listener for redirection if notification_type is 'order'
                                toast.addEventListener('click', function () {
                                    if (notification.notification_type === 'order') {
                                        markAsRead(notification.id);
                                        window.location.href = '/admin/manage-orders';
                                    }
                                });

                                // Append the toast to the notification list
                                notificationList.appendChild(toast);
                            });
                        });
                }
                function markAsRead(notificationId) {
                    fetch(`/notificaion/mark-as-read/${notificationId}`, {
                        method: 'GET'
                    }).then(() => {
                        fetchNotifications(); // Refresh notifications after marking one as read
                    });
                }

            // Helper function to format time ago
            function timeAgo(date) {
                const seconds = Math.floor((new Date() - date) / 1000);
                let interval = seconds / 31536000;

                if (interval > 1) return Math.floor(interval) + " years ago";
                interval = seconds / 2592000;
                if (interval > 1) return Math.floor(interval) + " months ago";
                interval = seconds / 86400;
                if (interval > 1) return Math.floor(interval) + " days ago";
                interval = seconds / 3600;
                if (interval > 1) return Math.floor(interval) + " hours ago";
                interval = seconds / 60;
                if (interval > 1) return Math.floor(interval) + " minutes ago";
                return "Just now";
            }

            fetchNotifications(); // Initial fetch
            setInterval(fetchNotifications, 30000); // Fetch notifications every 30 seconds
        });

        </script>
    </body>
</html>
