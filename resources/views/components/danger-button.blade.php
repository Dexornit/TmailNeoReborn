<button {{ $attributes->merge(['type' => 'button', 'class' => 'neo-btn neo-btn--danger inline-flex items-center justify-center px-4 py-2 font-bold text-xs uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
