<!-- year-selector.blade.php -->
<div class="year-selector">
    <div class="d-flex justify-content-between align-items-center year-navigation">
        <h5><i class="fas fa-calendar-alt"></i> Filter by Years : <span class="year-label"> <?= date('Y') ?></span></h5>

        <!-- Year Pills -->
        <div class="year-pills" id="yearPills">
            @foreach ($data['list_year'] as $year)
                @if ($year == date('Y'))
                    @php
                        $isActive = 'active';
                    @endphp
                @else
                    @php
                        $isActive = '';
                    @endphp
                @endif
                <button class="year-pill {{ $isActive }}" data-year="{{ $year }}">{{ $year }}</button>
            @endforeach
        </div>

        <!-- Year Dropdown (for mobile/overflow) -->
        <div class="year-dropdown">
            <button id="yearDropdownToggle" class="year-dropdown-toggle">
                Select Year <i class="fas fa-chevron-down"></i>
            </button>
            <div id="yearDropdownMenu" class="year-dropdown-menu">
                <!-- Search Input -->
                <div class="year-search">
                    <input type="text" id="yearSearchInput" class="year-search-input" placeholder="Search year...">
                    <i class="fas fa-search year-search-icon"></i>
                </div>
                <!-- Year Options (populated via JS) -->
                <div id="yearDropdownItems"></div>
            </div>
        </div>
    </div>
</div>