<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Bot</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="body">
    <div class="container">
        <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="{{url('./images/icon.png')}}" alt="Logo" class="mr" style="width: 40px; height: auto;">
                    <h5 class="mb-0">Chat Bot</h5>
                    <button class="btn justify-content-md-end" id="clearChat">
                        <i class="ri-delete-bin-line custom-icon"></i> Clear Chat
                    </button>
                </div>
            </div>
            <div class="card-body" id="chat-box" style="height: 400px; overflow-y: auto;">
                <!-- Messages will be appended here -->
            </div>
            <div id="popup-area" style="position: relative; z-index: 9999;"></div>
            <div class="card-footer d-flex flex-column">
                <div class="suggestions mb-2 d-flex justify-content-start">
                    <button class="btn btn-outline-primary suggestion-btn me-2">Hi Axil Bot!</button>
                    <button class="btn btn-outline-primary suggestion-btn me-2">Can you suggest best colors for women clothing?</button>
                    <button class="btn btn-outline-primary suggestion-btn">Can you suggest outfits?</button>
                </div>
                <div class="d-flex align-items-center">
                    <input type="text" class="form-control form-control-lg" id="message" placeholder="Type or say your message...">
                    <button class="btn btn-outline-secondary ms-2" id="voiceButton">
                        <i class="ri-mic-line fs-4"></i>
                    </button>
                    <button class="btn btn-primary ms-2" id="sendMessage">Send</button>
                </div>
            </div>
        </div>
    </div>
    

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function () {
            var recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
            recognition.lang = 'en-US';
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            $('#voiceButton').click(function () {
                recognition.start();
            });

            recognition.onresult = function (event) {
                var speechResult = event.results[0][0].transcript;
                $('#message').val(speechResult);
            };

            recognition.onerror = function (event) {
                console.log('Speech recognition error: ', event.error);
            };

            $('#sendMessage').click(function () {
                var message = $('#message').val();
                if (message.trim() === '') return;

                $('#chat-box').append(`
                    <div class="d-flex justify-content-end mb-3">
                        <div class="bg-primary text-white rounded-3 p-2">
                            ${message}
                        </div>
                    </div>
                `);

                $('#message').val('');

                $.ajax({
                    url: '/admin/chat-response',
                    type: 'POST',
                    data: {
                        message: message,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.choices && response.choices.length > 0) {
                            $('#chat-box').append(`
                                <div class="d-flex justify-content-start mb-3">
                                    <div class="bg-light rounded-3 p-2">
                                        ${response.choices[0].message.content}
                                        <span class="speaker-icon" data-message="${response.choices[0].message.content}">
                                            <i class="fas fa-volume-up"></i>
                                        </span>
                                    </div>
                                </div>
                            `);
                        } else {
                            $('#chat-box').append(`
                                <div class="d-flex justify-content-start mb-3">
                                    <div class="bg-light rounded-3 p-2">
                                        No response from server.
                                    </div>
                                </div>
                            `);
                        }
                        $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
                    },
                    error: function () {
                        $('#chat-box').append(`
                            <div class="d-flex justify-content-start mb-3">
                                <div class="bg-light rounded-3 p-2">
                                    An error occurred.
                                </div>
                            </div>
                        `);
                        $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
                    }
                });
            });

            $('#message').keypress(function (e) {
                if (e.which == 13) {
                    $('#sendMessage').click();
                }
            });

            $(document).on('click', '.speaker-icon', function () {
                var text = $(this).data('message');
                var speech = new SpeechSynthesisUtterance(text);
                speech.lang = 'en-US';
                window.speechSynthesis.speak(speech);
            });

            $('.suggestion-btn').click(function () {
                var suggestion = $(this).text();
                $('#message').val(suggestion);
            });

            $('#clearChat').click(function () {
                $('#chat-box').empty();
            });

            function showPopupMessage(message) {
                var popup = $(`
                    <div class="popup-message bg-primary text-white p-2 rounded-3">
                        ${message}
                        <button class="close-popup" style="background: none; border: none; color: white; font-size: 1rem; float: right;">&times;</button>
                    </div>
                `);

                $('#popup-area').append(popup);

                popup.find('.close-popup').click(function () {
                    popup.remove();
                });
            }

            $('#sendMessage').click(function () {
                var message = $('#message').val();
                if (message.trim() === '') return;

                showPopupMessage('Message sent: ' + message);
            });
        });
    </script>
    <style>
        .body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #f87e5f, #feb47b);
        }

        #popup-area {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        .popup-message {
            background-color: #007bff;
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            opacity: 0.9;
            max-width: 300px;
            word-wrap: break-word;
        }

        .close-popup {
            cursor: pointer;
        }
    </style>
    <footer>
        <p class="text-center mt-4">&copy;  Nisal Gunawardhana, Microsoft Learn Student Ambassador,Postman Student Leader</p>
    </footer>
</body>
</html>
