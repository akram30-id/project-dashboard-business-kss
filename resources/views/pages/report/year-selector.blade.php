<!-- year-selector.blade.php -->

<div class="year-selector">
    <div class="d-flex justify-content-between align-items-center year-navigation w-100 flex-wrap">
        <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Filter by Years : <span class="year-label">
                <?= date('Y') ?></span></h5>

        <!-- Year Pills -->
        <div class="mx-auto">
            <div class="year-pills" id="yearPills">
                @foreach ($data['list_year'] as $year)
                    <button class="year-pill {{ $year == date('Y') ? 'active' : '' }}"
                        data-year="{{ $year }}" id="year{{ $year }}">{{ $year }}</button>
                @endforeach
            </div>
        </div>

        <!-- Year Dropdown (for mobile/overflow) -->
        <div class="year-dropdown ms-auto">
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

<script>
    $(".year-pill").click(function() {
        const year = $(this).data("year");

        if (year == {{ date('Y') }}) {
            // $(".year-pill").removeClass("active");
            $(this).addClass("active");
        } else {
            $(".year-pill").removeClass("active");
            $(this).addClass("active");
        }
    });
</script>