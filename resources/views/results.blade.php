<div>
    <input type="text" id="searchInput" placeholder="Search">
</div>

<ul id="searchResults">
    @foreach($products as $product)
        <li>{{ $product->name }}</li>
    @endforeach
</ul>

<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchTerm = this.value;

        fetch(`{{ route('search') }}?searchTerm=${searchTerm}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('searchResults').innerHTML = data;
            })
            .catch(error => console.error(error));
    });
</script>