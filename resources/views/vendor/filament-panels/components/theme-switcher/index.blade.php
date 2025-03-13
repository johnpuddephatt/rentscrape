<div
    x-data="{ theme: null }"
    x-init="
        $watch('theme', () => {
            $dispatch('theme-changed', theme)
            if(theme === 'sidebar') {
                document.documentElement.classList.add('theme-sidebar');
            }
            else {
                document.documentElement.classList.remove('theme-sidebar')
            }
        })


        theme = localStorage.getItem('theme') || @js(filament()->getDefaultThemeMode()->value)
    "
    class="fi-theme-switcher grid grid-flow-col gap-x-1">
    <x-filament-panels::theme-switcher.button
        icon="heroicon-m-sun"
        theme="light" />

    <x-filament-panels::theme-switcher.button
        icon="heroicon-m-moon"
        theme="dark" />

    <x-filament-panels::theme-switcher.button
        icon="heroicon-m-cube"
        theme="sidebar" />

    <x-filament-panels::theme-switcher.button
        icon="heroicon-m-computer-desktop"
        theme="system" />
</div>