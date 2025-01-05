<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <title>AI</title>
</head>
<body>
    <style>
        body{
            font-family: merriweather;
            background-color: rgb(238, 236, 226);
        }
    </style>

        <!-- Header Halaman -->
    <div class="d-flex mt-3 me-4 ms-3 header">
        <div class="row w-100">
            <a href="beranda.php" class="col-1">
                <div>
                    <img src="assets/back.png" alt="Kembali" width="30px">
                </div>
            </a>
            <div class="col">
                <h4 style="font-weight: bold; margin: 0; padding: 0;">Gemini AI</h4>
                <div>
                        <p class="loading text-muted p-0 m-0" id="loading" style="font-size: 13px; z-index: 10;display: none;">Memproses permintaan Anda</p>
                        <p class="text-muted p-0 m-0" style="font-size: 13px; z-index: 10;" id="tersedia">Gemini tersedia</p>
                </div>
                <style>
                    @keyframes smoothBlink {
                        0% { opacity: 1; }
                        50% { opacity: 0.3; }
                        100% { opacity: 1; }
                    }

                    .loading {
                        animation: smoothBlink 1.5s ease-in-out infinite;
                    }

                    /* Optional: tambahkan transisi smooth saat elemen muncul/hilang */
                    #loading, #tersedia {
                        transition: opacity 0.3s ease-in-out;
                    }                    
                    
                    .header{
                        position: fixed;
                    }
                </style>
            </div>
        </div>
    </div>

    <!-- salam pembuka -->
    <div class="greeting" id="greeting">
        <img src="assets/ai_chat.png" alt="" class="ai" width="60px">
        <p>Halo, Bapak Ibu Guru. Ada yang bisa saya bantu hari ini?</p>
    </div>
    <!-- style pembuka greeting ai -->
     <style>
        .ai {
            filter: brightness(1000%);
        }
        .greeting {
            text-align: center;
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 10px;
            color: gray;
            font-size: 14px;
            display: block; /* Menampilkan salam pembuka secara default */
            z-index: 10; /* Pastikan elemen berada di atas konten lain */
        }
     </style>

    <!-- ini isi dari chat -->
    <div class="container" style="padding-top: 4rem;">
        <!-- Chat Messages Container -->
        <div id="chat-container" class="card-body chat-container mt-2 pe-3" style="overflow-y: auto; overflow-x: hidden; padding-bottom: 80px;">
            <!-- Pesan chat akan ditampilkan di sini -->
        </div>
        
        <div class="text-center">
        <div class="input-area" style="position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 90%; z-index: 10;">
            <div class="card-footer p-2 rounded-3" style="background-color:white;">
                <div class="input-group d-flex justify-content-center">
                    <textarea id="user-input" class="form-control border-0" 
                        style="background-color: transparent; resize: none; overflow-y: hidden; min-height: 40px; max-height: 200px;" 
                        placeholder="Tulis apa yang ada di benak Anda" rows="1"></textarea>
                    <button id="send-button" class="btn bi-send rounded text-white" style="background-color: rgb(218, 119, 86);"></button>
                </div>
            </div>    
            <div class="pt-2 pb-2">
                <p class="text-muted p-0 m-0" style="font-size: 10px;">Gemini dapat membuat kesalahan, selalu cek setiap respons.</p>
            </div>
        </div>
        </div>
    </div>

    <!-- style container chat -->
     <style>
        /* mengatur input text user */
        .input-group {
            display: flex;
            align-items: center; /* Menyelaraskan ikon dan input secara vertikal */
        }

        #user-input {
            flex-grow: 1; /* Membuat input mengisi ruang yang tersedia */
        }

        #send-button {
            background-color: rgb(218, 119, 86); /* Menentukan warna tombol kirim */
        }

        /* Mengatur chat container untuk menyesuaikan sisa ruang */
        #chat-container {
            height: calc(80vh - 60px); /* Menggunakan sisa ruang di bawah setelah area input */
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Input Area di bawah */
        .input-area {
            position: fixed;
            bottom: -10%;
            left: 50%; /* Memindahkan sisi kiri input area ke tengah layar */
            transform: translateX(5%); /* Menggeser input area ke kiri untuk menengahkan */
            width: 90%; /* Lebar input area yang bisa disesuaikan */
            max-width: 500px; /* Membatasi lebar maksimal untuk layar besar */
            z-index: 10; /* Pastikan input berada di atas konten lain */
        }

        /* Menyesuaikan input agar responsif di berbagai layar */
        @media (max-width: 768px) {
            .input-group {
                width: 95%;
            }

            #user-input {
                font-size: 14px; /* Menyesuaikan ukuran font pada input */
            }

            .btn {
                font-size: 16px; /* Menyesuaikan ukuran tombol kirim */
            }
        }

        /* Memastikan chat-container tidak tertutup oleh input */
        body {
            margin-bottom: 80px; /* Memberi ruang tambahan di bawah agar tidak tertutup oleh fixed input */
        }
     </style>
     <!-- script kontainer chat -->
     <script>
        document.getElementById('user-input').addEventListener('input', function() {
            // Reset height
            this.style.height = 'auto';
            
            // Get the computed styles for the element
            const computed = window.getComputedStyle(this);
            
            // Calculate the height
            const height = parseInt(computed.getPropertyValue('border-top-width'), 10)
                        + parseInt(computed.getPropertyValue('padding-top'), 10)
                        + this.scrollHeight
                        + parseInt(computed.getPropertyValue('padding-bottom'), 10)
                        + parseInt(computed.getPropertyValue('border-bottom-width'), 10);
            
            // Apply the height
            this.style.height = `${Math.min(height, 200)}px`;
        });

        // Trigger the event on pressing Enter (optional)
        document.getElementById('user-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('send-button').click();
            }
        });
    </script>

     <!-- modal untuk kebijakan
            <div class="modal fade text-black" id="modalai" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered text-start">
                    <div class="modal-content">
                        <div class="modal-body">
                        <p style="font-size: 14px;">Sebelum melanjutkan, diharapkan untuk dapat memahami kebijakan di bawah :</p>
                                <div d-grid>
                                    <div class="container">
                                        <div class="row align-items-center">
                                            <div class="col justify-content-center text-center">
                                                <img src="assets/beta.png" alt="" width="40px">
                                            </div>
                                            <div class="col-9">
                                            <p class="p-0 m-0"><strong>Eksperimental</strong></p>
                                                <p style="font-size:12px;">Fitur Gemini AI dalam Absensi SMAGA saat ini bersifat eksperimental. Keberadaan fitur ini dapat dihentikan sewaktu-waktu sesuai dengan kebutuhan Anda dan efisiensi sistem.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div d-grid>
                                    <div class="container">
                                        <div class="row align-items-center">
                                            <div class="col justify-content-center text-center">
                                                <img src="assets/historyoff.png" alt="" width="40px">
                                            </div>
                                            <div class="col-9">
                                            <p class="p-0 m-0"><strong>Riwayat Non-Aktif</strong></p>
                                                <p style="font-size:12px;">Percakapan Anda dengan Gemini AI tidak akan disimpan. Pastikan untuk mencatat atau menyimpan percakapan penting sebelum menutup sesi.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        </div>
                        <div class="modal-footer d-flex justify-content-between" role="group">
                            <button type="button" class="btn flex-fill text-white" style="background-color: rgb(218, 119, 86);" data-bs-dismiss="modal">Saya mengerti</button>
                        </div>
                    </div>
                </div>
            </div> -->
            <!-- script untuk modal ai -->
             <script>
            // // Menunggu hingga halaman sepenuhnya dimuat
            // document.addEventListener("DOMContentLoaded", function() {
            //     // Menampilkan modal secara otomatis setelah halaman dimuat
            //     var myModal = new bootstrap.Modal(document.getElementById('modalai'), {
            //         keyboard: false // Bisa menyesuaikan, jika tidak ingin modal hilang dengan menekan tombol Escape
            //     });
            //     myModal.show(); // Menampilkan modal
            // });

            // Cek apakah modal sudah ditampilkan dalam sesi ini
    //         if (!sessionStorage.getItem('modalShown')) {
    //             document.addEventListener("DOMContentLoaded", function() {
    //                 // Menampilkan modal secara otomatis setelah halaman dimuat
    //                 var myModal = new bootstrap.Modal(document.getElementById('modalai'), {
    //                     keyboard: false // Bisa menyesuaikan, jika tidak ingin modal hilang dengan menekan tombol Escape
    //                 });
    //                 myModal.show(); // Menampilkan modal
                    
    //                 // Menyimpan informasi bahwa modal sudah ditampilkan untuk sesi ini
    //                 sessionStorage.setItem('modalShown', 'true');
    //     });
    // }
             </script>

            <script>
            // Elemen DOM
            const chatContainer = document.getElementById('chat-container');
            const userInput = document.getElementById('user-input');
            const sendButton = document.getElementById('send-button');
            const greeting = document.getElementById('greeting'); 

            // Gambar profil
            const userImage = 'assets/akunai.png';
            const aiImage = 'assets/ai_chat.png';

            // Fungsi untuk memperbarui visibilitas salam pembuka
            function updateGreetingVisibility() {
                if (chatContainer.children.length === 0) {
                    greeting.style.display = 'block';
                } else {
                    greeting.style.display = 'none';
                }
            }

            // Fungsi untuk menampilkan teks secara bertahap
            function typewriterEffect(element, text) {
                return new Promise(resolve => {
                    const words = text.split(' ');
                    let currentText = '';
                    let wordIndex = 0;
                    
                    const typing = setInterval(() => {
                        if (wordIndex < words.length) {
                            currentText += words[wordIndex] + ' ';
                            element.innerHTML = currentText;
                            wordIndex++;
                        } else {
                            clearInterval(typing);
                            resolve();
                        }
                    }, 50);
                });
            }

            // Fungsi untuk menambahkan pesan ke chat
            function addMessage(sender, text) {
                return new Promise((resolve) => {
                    const messageWrapper = document.createElement('div');
                    messageWrapper.classList.add(
                        'd-flex', 
                        'mb-3', 
                        sender === 'user' ? 'justify-content-end' : 'justify-content-start'
                    );

                    // Tambahkan parsing untuk line break
                    const parsedText = text
                        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                        // Tambahkan line break untuk setiap nomor list
                        .replace(/(\d+\.\s)/g, '<br>$1')
                        // Tambahkan line break untuk simbol bullet point
                        .replace(/(\*\s)/g, '<br>•')
                        // Tambahkan line break untuk tanda bintang di awal baris
                        .replace(/(\n\*)/g, '<br>•')
                        // Ganti newline biasa dengan line break HTML
                        .replace(/\n/g, '<br>');

                    messageWrapper.innerHTML = `
                        <div class="d-flex align-items-center pt-1 pb-1 p-2 rounded-4 ${sender === 'user' ? 'flex-row-reverse' : ''}" style="background-color: white; max-width:15rem">
                            <img src="${sender === 'user' ? userImage : aiImage}" 
                                class="chat-profile bg-white ms-2 me-2 rounded-circle border" 
                                alt="${sender} profile"
                                style="width: 20px; height: 20px; object-fit: cover;">
                            <div class="chat-bubble rounded p-2 align-content-center ${sender === 'user' ? 'user-bubble' : 'ai-bubble'}">
                                <p style="font-size: 12px; margin: 0; padding: 0;"></p>
                            </div>
                        </div>
                    `;

                    chatContainer.appendChild(messageWrapper);

                    if (sender === 'ai') {
                        const textElement = messageWrapper.querySelector('p');
                        typewriterEffect(textElement, parsedText)
                            .then(() => {
                                chatContainer.scrollTop = chatContainer.scrollHeight;
                                updateGreetingVisibility();
                                resolve();
                            });
                    } else {
                        messageWrapper.querySelector('p').innerHTML = parsedText;
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                        updateGreetingVisibility();
                        resolve();
                    }
                });
            }

            // Fungsi untuk mendapatkan respons AI dari Gemini
            async function getAIResponse(userMessage) {
                const API_KEY = 'AIzaSyAm6yuSvkKYnjmlqor8HjciqFiFAwahUgM';
                const API_ENDPOINT = `https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=${API_KEY}`;

                try {
                    const response = await fetch(API_ENDPOINT, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            contents: [{
                                parts: [{
                                    text: userMessage
                                }]
                            }]
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Gagal mendapatkan respons dari Gemini');
                    }

                    const data = await response.json();
                    return data.candidates[0].content.parts[0].text || 'Maaf, tidak dapat memproses permintaan.';
                } catch (error) {
                    console.error('Error fetching Gemini response:', error);
                    return 'Maaf, terjadi kesalahan dalam komunikasi dengan AI.';
                }
            }

            // Fungsi utilitas untuk loading state
            function showLoader() {
                document.getElementById('loading').style.display = 'block';
            }

            function hideLoader() {
                document.getElementById('loading').style.display = 'none';
            }

            function showTersedia() {
                document.getElementById('tersedia').style.display = 'block';
            }

            function hideTersedia() {
                document.getElementById('tersedia').style.display = 'none';
            }

            // Fungsi utama untuk mengirim pesan
            async function sendMessage() {
                const userMessage = userInput.value.trim();
                if (userMessage === '') return;

                try {
                    // Tampilkan pesan pengguna
                    await addMessage('user', userMessage);
                    
                    // Bersihkan input
                    userInput.value = '';
                    
                    // Update UI state
                    updateGreetingVisibility();
                    hideTersedia();
                    showLoader();

                    // Dapatkan dan tampilkan respons AI
                    const aiResponse = await getAIResponse(userMessage);
                    hideLoader();
                    showTersedia();
                    
                    await addMessage('ai', aiResponse);
                } catch (error) {
                    console.error('Error in sendMessage:', error);
                    hideLoader();
                    showTersedia();
                }
            }

            // Event listeners
            sendButton.addEventListener('click', sendMessage);

            userInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    sendMessage();
                }
            });

            // Initialize
            updateGreetingVisibility();
                    </script>
            </div>
            


</body>
</html>