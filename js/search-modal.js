(function(){

  // Link to open modal
  document.querySelectorAll("a.search-modal-link").forEach(function(modalLink) {
    modalLink.addEventListener("click", function(e){
      e.preventDefault();
      var modalElementID = modalLink.getAttribute("data-modal");
      document.getElementById(modalElementID).style.display = 'block';
      document.querySelector('#' + modalElementID + ' input.text').focus();
    });
  });

  // Close modal on background click
  document.querySelectorAll(".search-modal .bg").forEach(function(modalBackground) {
    modalBackground.addEventListener("click", function(){
      document.querySelectorAll(".search-modal").forEach(function(searchModal) {
        searchModal.style.display = 'none';
      });
    });
  });

  // Close modal on escape key
  document.addEventListener('keyup', function(e){
    if(e.key === 'Escape'){
      document.querySelectorAll(".search-modal").forEach(function(searchModal) {
        searchModal.style.display = 'none';
      });
    }
  });

})();
