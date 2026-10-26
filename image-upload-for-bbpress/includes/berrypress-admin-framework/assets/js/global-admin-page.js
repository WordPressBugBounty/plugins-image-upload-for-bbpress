jQuery(document).ready(function($) {
	const $bpToggleBtn  = $(".berrypress-toggle-sidebar");
	const $bpSidebar    = $("#berrypress-sidebar");
	const $mobileButton = $("#berrypress-toggle-menu-mobile");
	const pageSlug = (new URLSearchParams(location.search)).get('page');

	const $topBars = $(".berrypress-top-bar");

	$topBars.append(
		$("<button>", {
			type: "button",
			class: "berrypress-top-bar-dismiss berrypress-btn-reset",
			"aria-label": "Dismiss this notice"
		}).append($("<i>", {
			class: "berrypress-icon-close",
			"aria-hidden": "true"
		}))
	);

	$topBars.on("click", ".berrypress-top-bar-dismiss", function() {
		const cookieName = "image_upload_for_bbpress_top_bar_dismissed" + ($(this).closest(".berrypress-top-bar").hasClass("berrypress-top-bar-promo") ? "_bf" : "");
		document.cookie = cookieName + "=1; Max-Age=86400; Path=/; SameSite=Lax" +
			(window.location.protocol === "https:" ? "; Secure" : "");
		$(".berrypress-settings-container").addClass("berrypress-top-bar-dismissed");
	});

	function berrypress_isDesktopSidebar() {
		return $(window).width() >= 700;
	}

	function berrypress_saveSidebarState(collapsed) {
		try {
			if (pageSlug) {
				document.cookie = 'berrypress_sidebar_collapsed_' + pageSlug + '=' + (collapsed ? '1' : '0') + ';path=/;max-age=31536000;SameSite=Lax';
			}
		} catch (e) {}
	}

	function berrypress_applySidebarState() {
		if (berrypress_isDesktopSidebar()) {
			// Only override the server-rendered state once the visitor has actually
			// toggled the sidebar (i.e. a cookie was saved for this page). Without that,
			// leave whatever PHP rendered (e.g. permanently collapsed via a filter) alone.
			if (pageSlug && document.cookie) {
				if (document.cookie.includes('berrypress_sidebar_collapsed_' + pageSlug + '=1')) {
					$bpSidebar.addClass("collapsed");
				} else if (document.cookie.includes('berrypress_sidebar_collapsed_' + pageSlug + '=0')) {
					$bpSidebar.removeClass("collapsed");
				}
			}
		} else {
			$bpSidebar.addClass("collapsed");
		}
	}

	function berrypress_toggleSidebar(persist) {
		$bpSidebar.toggleClass("collapsed");
		if (persist && berrypress_isDesktopSidebar()) {
			berrypress_saveSidebarState($bpSidebar.hasClass("collapsed"));
		}
	}

	$bpToggleBtn.on("click", function() {
		berrypress_toggleSidebar(true);
	});

	$mobileButton.on("click", function() {
		berrypress_toggleSidebar(false);
	});

	berrypress_applySidebarState();
	$(window).on("resize", berrypress_applySidebarState);

	// Ensure custom settings button triggers WordPress default settings link
	$('#berrypress-show-settings-link').on('click', function(event) {
		event.preventDefault();
		$('#show-settings-link').trigger('click');
	});

	// Check Table Width & Apply Class
	function berrypress_checkTableWidth() {
		$(".berrypress-table").each(function() {
			if ($(this).width() < 800) {
				$(this).addClass("berrypress-table-small");
			} else {
				$(this).removeClass("berrypress-table-small");
			}
		});
	}

	berrypress_checkTableWidth();
	$(window).on("resize", berrypress_checkTableWidth);

	function berrypress_adjustSidebarHeight() {
		var sidebar = document.getElementById('berrypress-sidebar');

		if (sidebar) {
			var viewportWidth = window.innerWidth;
			var viewportHeight = window.innerHeight;

			// Skip on small screens (e.g. mobile)
			if (viewportWidth < 600) {
				$(sidebar).css('min-height', '');
				return;
			}

			var rectTop = sidebar.getBoundingClientRect().top;

			if (Math.abs(rectTop - 32) < 2) {
				$(sidebar).css('min-height', '');
			} else {
				var visibleHeight = viewportHeight - rectTop;
				$(sidebar).css('min-height', visibleHeight + 'px');
			}
		}
	}

	berrypress_adjustSidebarHeight();
	$(window).on('resize scroll', berrypress_adjustSidebarHeight); // scroll też!
});

document.fonts.ready.then(() => {
	document.documentElement.classList.add('berrypress-font-loaded');
});
