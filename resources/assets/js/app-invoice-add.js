/**
 * App Invoice - Add
 */
'use strict';

(function () {
  const invoiceItemPriceList = document.querySelectorAll('.invoice-item-price'),
    invoiceItemQtyList = document.querySelectorAll('.invoice-item-qty'),
    invoiceDateList = document.querySelectorAll('.date-picker'),
    invoiceDate = document.querySelector('.invoice-date'),
    dueDate = document.querySelector('.due-date');

  // Price
  if (invoiceItemPriceList) {
    invoiceItemPriceList.forEach(function (invoiceItemPrice) {
      new Cleave(invoiceItemPrice, {
        delimiter: '',
        numeral: true
      });
    });
  }

  // Qty
  if (invoiceItemQtyList) {
    invoiceItemQtyList.forEach(function (invoiceItemQty) {
      new Cleave(invoiceItemQty, {
        delimiter: '',
        numeral: true
      });
    });
  }

  // Datepicker
  if (invoiceDateList) {
    invoiceDateList.forEach(function (invoiceDateEl) {
      invoiceDateEl.flatpickr({
        monthSelectorType: 'static'
      });
    });
  }
  if (invoiceDate) {
    invoiceDate.flatpickr({
      monthSelectorType: 'static'
    });
  }
  if (dueDate) {
    dueDate.flatpickr({
      monthSelectorType: 'static'
    });
  }
})();

// repeater (jquery)
$(function () {
  var applyChangesBtn = $('.btn-apply-changes'),
    discount,
    tax1,
    tax2,
    discountInput,
    tax1Input,
    tax2Input,
    sourceItem = $('.source-item'),
    adminDetails = {
      'App Design': 'Designed UI kit & app pages.',
      'App Customization': 'Customization & Bug Fixes.',
      'ABC Template': 'Bootstrap 4 admin template.',
      'App Development': 'Native App Development.'
    };

  // Function to show toast notification
  function showToast(message, type = 'warning') {
    // Check if toastr is available
    if (typeof toastr !== 'undefined') {
      toastr[type](message, '', {
        closeButton: true,
        tapToDismiss: false,
        rtl: true,
      });
    } else {
      // Fallback to alert if toastr is not available
      alert(message);
    }
  }

  // Prevent dropdown from closing on tax change
  $(document).on('click', '.tax-select', function (e) {
    e.stopPropagation();
  });

  // On tax change update it's value value
  function updateValue(listener, el) {
    listener.closest('.repeater-wrapper').find(el).text(listener.val());
  }

  // Apply item changes btn
  if (applyChangesBtn.length) {
    $(document).on('click', '.btn-apply-changes', function (e) {
      var $this = $(this);
      tax1Input = $this.closest('.dropdown-menu').find('#taxInput1');
      tax2Input = $this.closest('.dropdown-menu').find('#taxInput2');
      discountInput = $this.closest('.dropdown-menu').find('#discountInput');
      tax1 = $this.closest('.repeater-wrapper').find('.tax-1');
      tax2 = $this.closest('.repeater-wrapper').find('.tax-2');
      discount = $('.discount');

      if (tax1Input.val() !== null) {
        updateValue(tax1Input, tax1);
      }

      if (tax2Input.val() !== null) {
        updateValue(tax2Input, tax2);
      }

      if (discountInput.val().length) {
        $this
          .closest('.repeater-wrapper')
          .find(discount)
          .text(discountInput.val() + '%');
      }
    });
  }

  // Custom delete handler to check before deletion
  $(document).on('click', '[data-repeater-delete]', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const $deleteBtn = $(this);
    const $itemsContainer = $deleteBtn.closest('[data-repeater-list]');
    const $allItems = $itemsContainer.find('[data-repeater-item]');
    
    // Check if this would be deleting the last item
    if ($allItems.length <= 1) {
      showToast('لا يمكن حذف آخر عنصر. يجب أن يكون هناك عنصر واحد على الأقل.');
      return false;
    }
    
    // If not the last item, trigger the regular delete functionality
    $deleteBtn.closest('[data-repeater-item]').slideUp(function() {
      $(this).remove();
    });
  });

  // Repeater init
  if (sourceItem.length) {
    sourceItem.on('submit', function (e) {
      e.preventDefault();
    });
    
    // Custom counter to ensure unique IDs for select elements
    let itemCounter = 0;
    
    sourceItem.repeater({
      // Important: Use this parameter to ensure proper cloning
      defaultValues: {
        'select-initialized': false
      },
      show: function () {
        const $newItem = $(this);
        itemCounter++;
        
        // First make the item visible
        $newItem.slideDown();
        
        // Find any select2 elements and give them unique IDs
        $newItem.find('select.select2').each(function() {
          const $select = $(this);
          
          // Give a unique ID to prevent conflicts
          const uniqueId = 'select2-' + itemCounter + '-' + Math.floor(Math.random() * 1000);
          $select.attr('id', uniqueId);
          
          // Remove any previous select2 initialization
          if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
          }
          
          // Important: wait for DOM to be fully updated
          setTimeout(function() {
            $select.select2({
              placeholder: function() {
                return $select.data('placeholder') || 'اختر...';
              },
              allowClear: true,
              width: '100%',
              language: 'ar',
              dir: 'rtl',
              dropdownParent: $select.parent() // Important for proper positioning
            });
          }, 50);
        });
        
        // Initialize Cleave.js for new input fields
        $newItem.find('.invoice-item-price, .invoice-item-qty').each(function() {
          new Cleave(this, {
            delimiter: '',
            numeral: true
          });
        });
        
        // Initialize tooltip on load of each item
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl);
        });
      },
      // We'll override the built-in delete functionality with our custom handler
      hide: function (e) {
        // The deletion logic is now handled by our custom click handler
        return false;
      }
    });
  }

  // Initialize Select2 for initial elements
  $('.select2').each(function() {
    const $select = $(this);
    
    // Skip if already initialized
    if ($select.hasClass('select2-hidden-accessible')) {
      return;
    }
    
    $select.select2({
      placeholder: function() {
        return $select.data('placeholder') || 'اختر...';
      },
      allowClear: true,
      width: '100%',
      language: 'ar',
      dir: 'rtl'
    });
  });

  // Item details select onchange
  $(document).on('change', '.item-details', function () {
    var $this = $(this),
      value = adminDetails[$this.val()];
    if ($this.next('textarea').length) {
      $this.next('textarea').val(value);
    } else {
      $this.after('<textarea class="form-control" rows="2">' + value + '</textarea>');
    }
  });
});