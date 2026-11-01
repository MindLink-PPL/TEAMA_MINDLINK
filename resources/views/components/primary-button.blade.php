<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-[#0d9488] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-wider hover:bg-[#0f766e] focus:bg-[#0f766e] active:bg-[#115e59] focus:outline-none focus:ring-2 focus:ring-[#0d9488] focus:ring-offset-2 transition ease-in-out duration-150 shadow-2xs']) }}>
    {{ $slot }}
</button>
