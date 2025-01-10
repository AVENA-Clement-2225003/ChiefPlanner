@extends('extra.layout')

@section('extra.title', 'Edit')

@section('extra.content')
    <form action="{{ route('extra.homepage') }}" method="post">
        @csrf
        <label>Intitulé :
            <input name="intitule" type="text" placeholder="banane, lait, ..."/>
        </label>
        <label>Quantité :
            <input name="quantite" type="text" placeholder="200g, 1L, 3 tranches"/>
        </label>
        <input type="submit" value="Ajouter">
    </form>
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
