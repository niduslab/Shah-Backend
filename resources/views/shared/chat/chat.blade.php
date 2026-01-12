@extends('layouts.app')
@section('title', 'NidusCart - Manage Chats')
@section('content')

<section>
    <div class="container py-3 px-5">
        <div class="row" style="border: 1px solid #e0e0e0; border-radius: 10px; background-color: #fff; padding: 20px 50px;">
            <div class="col-md-4 col-lg-4 col-xl-4 mb-4 mb-md-0">
                <h5 class="font-weight-bold mb-3 text-center text-lg-start">Customers</h5>
                <div class="card">
                    <div class="card-body">
                        <ul class="list-unstyled mb-0" style="max-height: 500px; overflow-y: scroll;" id="all-chats">
                            <!-- Chat List Will Be Dynamically Loaded Here -->
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-5 col-lg-5 col-xl-8 card p-5" style="margin-top: 38px !important">
                <ul class="list-unstyled" style="max-height: 450px; overflow-y: scroll;" id="chat-messages">
                    <!-- Chat Messages Will Be Dynamically Loaded Here -->
                </ul>

                <div class="card-footer text-muted d-flex justify-content-start align-items-center p-3">
                    <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava3-bg.webp" alt="avatar 3" style="width: 40px; height: 100%;">
                    <input type="text" class="form-control form-control-lg" id="message-input" placeholder="Type message">
                    <a class="ms-3" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.7.4/axios.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>

<script>
    let chatId = null; // Set the chatId on customer selection

    function formatDate(createdAt) {
        const date = new Date(createdAt);
        const options = {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true // Display in 12-hour format with AM/PM
        };
        return date.toLocaleString('en-US', options);
    }

    function loadChats() {
        axios.get(`/api/customer-care/agent/all-chats`).then(response => {
            const chats = response.data;
            const chatList = document.getElementById('all-chats');
            chatList.innerHTML = ''; // Clear previous chat items

            chats.forEach(chat => {
                const chatHtml = `
                    <li class="p-2 border-bottom bg-body-tertiary">
                        <a href="#!" onclick="loadChatMessages(${chat.id})" class="d-flex justify-content-between">
                            <div class="d-flex flex-row">
                                <img src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/avatar-8.webp" alt="avatar"
                                    class="rounded-circle d-flex align-self-center me-3 shadow-1-strong" width="30">
                                <div class="pt-1">
                                    <p class="fw-bold mb-0">${chat.userName || 'Guest User'}</p>
                                    <p class="small text-muted">${chat.message || 'No message'}</p>
                                </div>
                            </div>
                            <div class="pt-1">
                                <p class="small text-muted mt-1">${formatDate(chat.created_at) || 'Unknown time'}</p>
                                <span class="badge bg-danger float-end">${chat.unreadCount || ''}</span>
                            </div>
                        </a>
                    </li>`;
                chatList.innerHTML += chatHtml;
            });
        }).catch(error => {
            console.error('Error loading chats:', error);
        });
    }

    loadChats();

    // Function to load messages when a customer is selected
    function loadChatMessages(selectedChatId) {
        chatId = selectedChatId;
        axios.get(`/api/messages/${chatId}`)
            .then(response => {
                const messages = response.data;
                console.log(messages);

                const messageContainer = document.getElementById('chat-messages');
                messageContainer.innerHTML = '';
                messages.forEach(message => {
                    const messageHtml = `
                        <li class="d-flex justify-content-between mb-2">
                            <div class="card w-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-bold mb-0">${message.sender_name ? message.sender_name : 'Guest User'}</p>
                                        <p class="text-muted small mb-0"><i class="far fa-clock"></i> ${formatDate(message.created_at)}</p>
                                    </div>
                                    <p class="mb-0">${message.message}</p>
                                </div>
                            </div>
                        </li>`;
                    messageContainer.innerHTML += messageHtml;
                });
            })
            .catch(error => console.error(error));
    }

    // Function to send a message
    function sendMessage() {
        const message = document.getElementById('message-input').value;

        let messageContent = {
            chat_id: chatId,
            sender_id: 1,
            sender_type: 'admin',
            message: message,
            status: 'sent'
        }


        axios.post(`/api/messages`, { chat_id: chatId,
            sender_id: 1,
            sender_type: 'admin',
            message: message,
            status: 'sent' })
            .then(response => {
                loadChatMessages(chatId); // Reload messages after sending
                document.getElementById('message-input').value = '';
            })
            .catch(error => console.error('Error sending message:', error));
    }

    // setInterval(() => {
    //     if (chatId) {
    //         loadChatMessages(chatId);
    //     }
    // }, 1500);

    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: 'b6d7a841355e3a19d08c', // Your Pusher App Key
        cluster: 'ap2', // Your Pusher App Cluster
        encrypted: true,
        authEndpoint: '/broadcasting/auth', // Add this for private channels
    });

    // Listen for the MessageSent event on the chat channel
    window.Echo.channel(`chat.${chatId}`)
        .listen('MessageSent', (event) => {
            console.log('New Message:', event);

            const messages = event.message;
            const messageContainer = document.getElementById('chat-messages');
            messageContainer.innerHTML = '';
            messages.forEach(message => {
                const messageHtml = `
                <li class="d-flex justify-content-between mb-2">
                    <div class="card w-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <p class="fw-bold mb-0">${message.sender_name ? message.sender_name : 'Guest User'}</p>
                            <p class="text-muted small mb-0"><i class="far fa-clock"></i> ${message.created_at}</p>
                        </div>
                        <p class="mb-0">${message.message}</p>
                    </div>
                    </div>
                </li>`;
                messageContainer.innerHTML += messageHtml;
            });
        });
</script>

@endsection
