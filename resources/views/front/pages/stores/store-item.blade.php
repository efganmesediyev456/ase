<div class="store-card-div">
    <a
            title="{{ $item->name }}-dən sifariş və Azərbaycana çatdırılma" target="_blank" href="{{ $item->cashback_link }}"
            style="background: #fff; display: flex; justify-content: center; align-items: center; border-radius: 10px; width: 110px; height: 100px; overflow: hidden"
            class="store-card {{ $key % 2 == 0 ? 'even-card' : 'odd-card' }}"
    >
        <img style="object-fit: cover" src="{{ $item->logo }}" alt="{{ $item->name }}">
    </a>
</div>