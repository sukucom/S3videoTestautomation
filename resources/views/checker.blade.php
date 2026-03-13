<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Link Checker | Premium AI Automation</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --glass-border: rgba(255, 255, 255, 0.1);
            --success: #22c55e;
            --error: #ef4444;
            --warning: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: var(--bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.15) 0px, transparent 50%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .container {
            width: 100%;
            max-width: 900px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        header {
            text-align: center;
            margin-bottom: 3rem;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p.subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .upload-area {
            border: 2px dashed var(--glass-border);
            border-radius: 16px;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            background: rgba(255, 255, 255, 0.02);
        }

        .upload-area:hover, .upload-area.drag-over {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .upload-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        #file-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 1rem;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 2rem;
        }

        .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
        }

        .btn:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .results-area {
            margin-top: 3rem;
            display: none;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th {
            background: rgba(255, 255, 255, 0.05);
            text-align: left;
            padding: 1rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        td {
            padding: 1rem;
            border-top: 1px solid var(--glass-border);
        }

        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-ok { background: rgba(34, 197, 94, 0.1); color: var(--success); }
        .status-denied { background: rgba(239, 68, 68, 0.1); color: var(--error); }
        .status-not_found { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .status-timeout { background: rgba(99, 102, 241, 0.1); color: var(--primary); }
        .status-error { background: rgba(239, 68, 68, 0.1); color: var(--error); }

        .loader {
            display: none;
            text-align: center;
            margin-top: 2rem;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        #file-name {
            margin-top: 1rem;
            font-weight: 600;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <header>
                <h1>AI Video Link Checker</h1>
                <p class="subtitle">Securely validate your personalized video assets in real-time.</p>
            </header>

            <form id="upload-form">
                @csrf
                <div class="upload-area" id="drop-zone">
                    <span class="upload-icon">📁</span>
                    <p>Drop your CSV file here or click to browse</p>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">Supports firstName, lastName, email, media_url</p>
                    <input type="file" name="csv_file" id="file-input" accept=".csv,.txt">
                    <div id="file-name"></div>
                </div>

                <button type="submit" class="btn" id="submit-btn" disabled>Start Analysis</button>
            </form>

            <div class="loader" id="loader">
                <div class="spinner"></div>
                <p style="margin-top: 1rem; color: var(--text-muted);">Analyzing URLs concurrently...</p>
            </div>

            <div class="results-area" id="results-area">
                <div class="results-header">
                    <h2>Analysis Results</h2>
                    <div id="stats" style="font-size: 0.9rem; color: var(--text-muted);"></div>
                </div>
                <div class="table-container">
                    <table id="results-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>URL</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="results-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('upload-form');
        const fileInput = document.getElementById('file-input');
        const fileNameDisplay = document.getElementById('file-name');
        const submitBtn = document.getElementById('submit-btn');
        const loader = document.getElementById('loader');
        const resultsArea = document.getElementById('results-area');
        const resultsBody = document.getElementById('results-body');
        const dropZone = document.getElementById('drop-zone');
        const statsDisplay = document.getElementById('stats');

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                fileNameDisplay.textContent = fileInput.files[0].name;
                submitBtn.disabled = false;
            }
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, e => {
                e.preventDefault();
                dropZone.classList.add('drag-over');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, e => {
                e.preventDefault();
                dropZone.classList.remove('drag-over');
            }, false);
        });

        dropZone.addEventListener('drop', e => {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            if (files.length > 0) {
                fileNameDisplay.textContent = files[0].name;
                submitBtn.disabled = false;
            }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            // UI Reset
            submitBtn.disabled = true;
            loader.style.display = 'block';
            resultsArea.style.display = 'none';
            resultsBody.innerHTML = '';

            try {
                const response = await fetch('/video/process', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                });

                const data = await response.json();

                if (data.success) {
                    renderResults(data.results);
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('An unexpected error occurred.');
            } finally {
                loader.style.display = 'none';
                submitBtn.disabled = false;
            }
        });

        function renderResults(results) {
            resultsArea.style.display = 'block';
            let okCount = 0;

            results.forEach(row => {
                if (row.status === 'OK') okCount++;
                
                const tr = document.createElement('tr');
                const statusClass = `status status-${row.status.toLowerCase()}`;
                
                tr.innerHTML = `
                    <td>${row.firstName} ${row.lastName || ''}</td>
                    <td>${row.email || '-'}</td>
                    <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <a href="${row.media_url}" target="_blank" style="color: var(--primary); text-decoration: none;">${row.media_url}</a>
                    </td>
                    <td><span class="${statusClass}">${row.status}</span></td>
                `;
                resultsBody.appendChild(tr);
            });

            statsDisplay.textContent = `${okCount} / ${results.length} valid links found`;
        }
    </script>
</body>
</html>
