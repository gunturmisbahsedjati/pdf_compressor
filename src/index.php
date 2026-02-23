<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AkuCintaPDF ❤</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .upload-area {
            border: 2px dashed #dc3545;
            padding: 60px 20px;
            border-radius: 10px;
            background: #fff;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-area:hover,
        .upload-area.dragover {
            background: #fdf5f6;
            border-color: #b02a37;
        }

        #loading-ui,
        #result-ui {
            display: none;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="mb-3">Kompres file PDF</h1>
                <p class="text-muted mb-4">Kurangi ukuran file PDF Anda dengan tetap mempertahankan kualitas terbaik.</p>

                <div id="upload-ui">
                    <div class="mb-4 text-start">
                        <h5 class="mb-3 text-center">Tingkat Kompresi:</h5>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="quality" id="quality_extreme" value="extreme" autocomplete="off">
                            <label class="btn btn-outline-danger p-3" for="quality_extreme">
                                <strong>Ekstrem</strong><br>
                                <small>Kualitas terendah, kompresi maksimal</small>
                            </label>

                            <input type="radio" class="btn-check" name="quality" id="quality_recommended" value="recommended" autocomplete="off" checked>
                            <label class="btn btn-outline-danger p-3" for="quality_recommended">
                                <strong>Rekomendasi</strong><br>
                                <small>Kualitas bagus, kompresi optimal</small>
                            </label>

                            <input type="radio" class="btn-check" name="quality" id="quality_less" value="less" autocomplete="off">
                            <label class="btn btn-outline-danger p-3" for="quality_less">
                                <strong>Rendah</strong><br>
                                <small>Kualitas tinggi, kompresi minimal</small>
                            </label>
                        </div>
                    </div>

                    <div class="upload-area mb-3" id="drop-zone">
                        <h4 class="text-danger">Pilih file PDF</h4>
                        <p class="text-muted">atau jatuhkan PDF di sini</p>
                        <input type="file" id="pdfFile" accept="application/pdf" style="display: none;">
                    </div>
                    <button class="btn btn-danger btn-lg px-5" onclick="document.getElementById('pdfFile').click()">Pilih File PDF</button>
                </div>

                <div id="loading-ui" class="py-5">
                    <div class="spinner-border text-danger mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                    <h4>Mengompresi PDF...</h4>
                    <p class="text-muted">Mohon tunggu sebentar.</p>
                </div>

                <div id="result-ui" class="py-4">
                    <h2 class="text-success mb-3">PDF Berhasil Dikompresi!</h2>
                    <p class="mb-1">Ukuran awal: <b id="size-original"></b> KB</p>
                    <p class="mb-1">Ukuran baru: <b id="size-compressed"></b> KB</p>
                    <p class="text-danger fw-bold mb-4">Anda menghemat <span id="size-savings"></span>% ruang!</p>

                    <a href="#" id="btn-download" class="btn btn-danger btn-lg px-5 mb-3">Unduh PDF yang Dikompres</a><br>
                    <button class="btn btn-outline-secondary" onclick="resetUI()">Kompres file lain</button>
                </div>

            </div>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('pdfFile');
        const uploadUI = document.getElementById('upload-ui');
        const loadingUI = document.getElementById('loading-ui');
        const resultUI = document.getElementById('result-ui');

        // Mencegah default behavior drag-and-drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Efek visual saat file di-drag
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
        });

        // Menangani saat file di-drop
        dropZone.addEventListener('drop', (e) => {
            let dt = e.dataTransfer;
            let files = dt.files;
            handleFiles(files);
        }, false);

        // Menangani saat file dipilih lewat tombol
        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            if (files.length === 0) return;
            const file = files[0];

            if (file.type !== 'application/pdf') {
                alert('Tolong unggah file berformat PDF.');
                return;
            }

            uploadFile(file);
        }

        function uploadFile(file) {
            // Ambil nilai kualitas yang dipilih user
            const selectedQuality = document.querySelector('input[name="quality"]:checked').value;

            // Tampilkan loading, sembunyikan area upload
            document.getElementById('upload-ui').style.display = 'none';
            document.getElementById('loading-ui').style.display = 'block';

            let formData = new FormData();
            formData.append('pdf_file', file);
            formData.append('quality', selectedQuality); // Kirim kualitas ke backend

            fetch('compress.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Sembunyikan animasi loading
                    document.getElementById('loading-ui').style.display = 'none';

                    if (data.success) {
                        // Tampilkan hasil
                        document.getElementById('result-ui').style.display = 'block';
                        document.getElementById('size-original').innerText = data.original_size;
                        document.getElementById('size-compressed').innerText = data.compressed_size;
                        document.getElementById('size-savings').innerText = data.savings;

                        // Set link manual
                        document.getElementById('btn-download').href = data.download_link;

                        // Auto-download
                        const autoDownloadLink = document.createElement('a');
                        autoDownloadLink.href = data.download_link;
                        autoDownloadLink.style.display = 'none';
                        document.body.appendChild(autoDownloadLink);
                        autoDownloadLink.click();
                        document.body.removeChild(autoDownloadLink);
                    } else {
                        alert('Error: ' + data.message);
                        resetUI();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghubungi server.');
                    resetUI();
                });
        }

        function resetUI() {
            fileInput.value = ''; // Reset input
            resultUI.style.display = 'none';
            loadingUI.style.display = 'none';
            uploadUI.style.display = 'block';
        }
    </script>

</body>

</html>