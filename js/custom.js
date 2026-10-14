jQuery(function($) {
  const $toggleBtns = $('.toggle-calendly-btn a, a.toggle-calendly-btn');
  const $calendlySection = $('.the-calendly');

  if (!$toggleBtns.length || !$calendlySection.length) return;

  const showText = 'Einen Termin vereinbaren';
  const hideText = 'Kalender ausblenden';

  $toggleBtns.on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();

    // 1. Toggle visibility
    $calendlySection.toggleClass('hidden');
    const isHidden = $calendlySection.hasClass('hidden');

    // 2. Change text ONLY on non-fixed buttons
    $toggleBtns
      .filter(function() {
        return $(this).closest('.fixed-title').length === 0;
      })
      .text(isHidden ? showText : hideText);

    // 3. Scroll to Calendly for ALL buttons (including fixed-title)
    if (!isHidden) {
      $calendlySection[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});