<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detector de Logradouros | FullStack Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body, html { height: 100%; margin: 0; overflow: hidden; }
        #map-container { background: linear-gradient(135deg, #f3f4f6 25%, #e5e7eb 100%); position: relative; }
        #google-map-embed { width: 100%; height: 100%; border: 0; display: none; }
        .fade-in { animation: fadeIn 0.5s ease-in forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-gray-100 font-sans flex flex-col md:flex-row h-screen">

    <div id="side-panel" class="w-full md:w-1/3 h-screen bg-white p-8 shadow-2xl flex flex-col z-10">
        
        <h1 id="main-title" class="text-2xl font-extrabold text-gray-800 mb-8 flex items-center gap-3">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span id="title-text">Mapear Endereço</span>
        </h1>
        
        <div id="form-section" class="space-y-6">
            <p class="text-gray-500 text-lg">Insira um CEP para localizar o logradouro e visualizar no mapa em tempo real.</p>
            <form id="cep-form" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">CEP</label>
                    <input type="text" id="cep" name="cep" maxlength="9" placeholder="00000-000" 
                           class="w-full border-2 border-gray-100 rounded-xl p-4 text-lg focus:ring-2 focus:ring-blue-400 outline-none transition-all">
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg transition-transform hover:scale-[1.01]">
                    Rastrear Endereço
                </button>
            </form>
        </div>

        <div id="result-section" class="hidden mt-4 space-y-6 fade-in">
            <div class="p-6 bg-blue-50 rounded-2xl border-l-4 border-blue-600">
                <h2 class="text-sm uppercase tracking-wider font-bold text-blue-600 mb-4">Dados Localizados</h2>
                <div class="space-y-4">
                    <div><span class="text-xs text-gray-400 uppercase font-bold">Logradouro</span><p id="logradouro" class="text-gray-800 font-medium text-lg">---</p></div>
                    <div><span class="text-xs text-gray-400 uppercase font-bold">Bairro</span><p id="bairro" class="text-gray-800 font-medium">---</p></div>
                    <div><span class="text-xs text-gray-400 uppercase font-bold">Cidade / UF</span><p id="cidade-uf" class="text-gray-800 font-medium">---</p></div>
                </div>
            </div>
        </div>
    </div>

    <div id="map-container" class="flex-1 h-full flex items-center justify-center">
        <div id="map-placeholder" class="text-center transition-opacity duration-500">
            <div class="bg-white/50 backdrop-blur-md p-8 rounded-3xl border border-white/20">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <p class="text-gray-400 font-medium italic">Aguardando entrada de dados...</p>
            </div>
        </div>
        <iframe id="google-map-embed" src=""></iframe>
        
        <button id="btn-reset" class="hidden absolute bottom-8 right-8 flex items-center gap-3 px-6 py-4 bg-white rounded-full shadow-2xl text-blue-600 hover:scale-105 transition-all z-20 group">
            <span class="font-bold text-sm uppercase tracking-wider">Pesquisar outro endereço</span>
            <div class="bg-blue-50 p-2 rounded-full group-hover:bg-blue-100 transition-colors">
                <svg class="w-6 h-6 group-hover:-rotate-45 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
        </button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cepInput = document.getElementById('cep');
            const titleText = document.getElementById('title-text');
            const formSection = document.getElementById('form-section');
            const resultSection = document.getElementById('result-section');
            const mapEmbed = document.getElementById('google-map-embed');
            const mapPlaceholder = document.getElementById('map-placeholder');
            const btnReset = document.getElementById('btn-reset');

            cepInput.addEventListener('input', (e) => {
                let v = e.target.value.replace(/\D/g, '');
                if (v.length > 5) v = v.slice(0, 5) + '-' + v.slice(5, 8);
                e.target.value = v;
            });

            document.getElementById('cep-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const cep = cepInput.value.replace(/\D/g, '');
                if (cep.length !== 8) return alert('CEP Inválido');

                try {
                    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                    const data = await response.json();
                    if (data.erro) throw new Error('CEP não encontrado');

                    document.getElementById('logradouro').innerText = data.logradouro || 'Não informado';
                    document.getElementById('bairro').innerText = data.bairro || 'Não informado';
                    document.getElementById('cidade-uf').innerText = `${data.localidade} - ${data.uf}`;

                    const query = encodeURIComponent(`${data.logradouro}, ${data.localidade}`);
                    mapEmbed.src = `https://maps.google.com/maps?q=${query}&output=embed`;

                    mapEmbed.onload = () => {
                        mapPlaceholder.classList.add('hidden');
                        mapEmbed.style.display = 'block';
                    };

                    // TROCA DO TÍTULO
                    titleText.innerText = "Endereço Mapeado";

                    formSection.classList.add('hidden');
                    resultSection.classList.remove('hidden');
                    btnReset.classList.remove('hidden');

                } catch (err) { alert(err.message); }
            });

            btnReset.addEventListener('click', () => {
                cepInput.value = '';
                // RESET DO TÍTULO
                titleText.innerText = "Mapear Endereço";

                formSection.classList.remove('hidden');
                resultSection.classList.add('hidden');
                btnReset.classList.add('hidden');
                mapEmbed.style.display = 'none';
                mapEmbed.src = '';
                mapPlaceholder.classList.remove('hidden');
                cepInput.focus();
            });
        });
    </script>
</body>
</html>