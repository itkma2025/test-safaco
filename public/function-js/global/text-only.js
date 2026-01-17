function filterTextOnly(input) {
    // Hanya izinkan huruf (a-z, A-Z) dan spasi
    input.value = input.value.replace(/[^a-zA-Z\s]/g, '');
    
    // Opsional: hilangkan spasi ganda
    input.value = input.value.replace(/\s+/g, ' ');
    
    // Opsional: trim spasi di awal/akhir
    input.value = input.value.trimStart();
  }