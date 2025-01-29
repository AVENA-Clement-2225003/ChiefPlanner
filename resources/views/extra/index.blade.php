@extends('extra.layout')

@section('extra.title', 'Edit')

@section('extra.content')
    <div style="display: flex; flex-direction: row; align-items: flex-start">
        <div class="widget logForm">
            <h2 class="widgetTitle">Ajouter</h2>
            <form action="{{ route('extra.add') }}" method="post" class="logForm">
                @csrf
                <div>
                    <label for="intitule">Intitulé :</label>
                    <div class="inputHolder">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-alphabet" viewBox="0 0 16 16">
                            <path d="M2.204 11.078c.767 0 1.201-.356 1.406-.737h.059V11h1.216V7.519c0-1.314-.947-1.783-2.11-1.783C1.355 5.736.75 6.42.69 7.27h1.216c.064-.323.313-.552.84-.552s.864.249.864.771v.464H2.346C1.145 7.953.5 8.568.5 9.496c0 .977.693 1.582 1.704 1.582m.42-.947c-.44 0-.845-.235-.845-.718 0-.395.269-.684.84-.684h.991v.538c0 .503-.444.864-.986.864m5.593.937c1.216 0 1.948-.869 1.948-2.31v-.702c0-1.44-.727-2.305-1.929-2.305-.742 0-1.328.347-1.499.889h-.063V3.983h-1.29V11h1.27v-.791h.064c.21.532.776.86 1.499.86Zm-.43-1.025c-.66 0-1.113-.518-1.113-1.28V8.12c0-.825.42-1.343 1.098-1.343.684 0 1.075.518 1.075 1.416v.45c0 .888-.386 1.401-1.06 1.401Zm2.834-1.328c0 1.47.87 2.378 2.305 2.378 1.416 0 2.139-.777 2.158-1.763h-1.186c-.06.425-.313.732-.933.732-.66 0-1.05-.512-1.05-1.352v-.625c0-.81.371-1.328 1.045-1.328.635 0 .879.425.918.776h1.187c-.02-.986-.787-1.806-2.14-1.806-1.41 0-2.304.918-2.304 2.338z"/>
                        </svg>
                        <input id="intitule" type="text" name="intitule" placeholder="banane, lait, ..." required>
                    </div>
                </div>

                <div>
                    <label for="quantite">Quantité :</label>
                    <div class="inputHolder">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-123" viewBox="0 0 16 16">
                            <path d="M2.873 11.297V4.142H1.699L0 5.379v1.137l1.64-1.18h.06v5.961zm3.213-5.09v-.063c0-.618.44-1.169 1.196-1.169.676 0 1.174.44 1.174 1.106 0 .624-.42 1.101-.807 1.526L4.99 10.553v.744h4.78v-.99H6.643v-.069L8.41 8.252c.65-.724 1.237-1.332 1.237-2.27C9.646 4.849 8.723 4 7.308 4c-1.573 0-2.36 1.064-2.36 2.15v.057zm6.559 1.883h.786c.823 0 1.374.481 1.379 1.179.01.707-.55 1.216-1.421 1.21-.77-.005-1.326-.419-1.379-.953h-1.095c.042 1.053.938 1.918 2.464 1.918 1.478 0 2.642-.839 2.62-2.144-.02-1.143-.922-1.651-1.551-1.714v-.063c.535-.09 1.347-.66 1.326-1.678-.026-1.053-.933-1.855-2.359-1.845-1.5.005-2.317.88-2.348 1.898h1.116c.032-.498.498-.944 1.206-.944.703 0 1.206.435 1.206 1.07.005.64-.504 1.106-1.2 1.106h-.75z"/>
                        </svg>
                        <input id="quantite" type="text" name="quantite" placeholder="200g, 1L, 3 tranches" required>
                    </div>
                </div>

                <button>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-plus-square" viewBox="0 0 16 16">
                        <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                    </svg>
                </button>
            </form>
        </div>
        <div class="widget">
            <table>
                <thead>
                <tr>
                    <td>Intitulé</td>
                    <td>Quantité</td>
                    <td>Delete</td>
                </tr>
                </thead>
                <tbody>
                @foreach($extraList as $extra)
                    <tr>
                        <td>{{ $extra->intitule }}</td>
                        <td>{{ $extra->quantite }}</td>
                        <td><form action="{{ route('extra.delete') }}" method="post">
                                @csrf
                                <input type="hidden" value="{{ $extra->intitule }}" name="intitule">
                                <input type="submit" value="X">
                            </form></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function modifyQty() {
            const qty_form = document.getElementById('Qform');
            const input = document.getElementById('intitule');

            qty_form.addEventListener('submit', (event) => {
                event.preventDefault(); // Prevent form submission initially
                const userInput = prompt("Combien on couté les courses ?");

                if (userInput === null || userInput.trim() === '') {
                    return;
                }

                input.value = userInput;
                qty_form.submit();
            });
        }
    </script>
@endsection
