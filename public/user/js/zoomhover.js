        const section = document.getElementById('background');
        const bgImage = section.querySelector('.interactive-bg');

        section.addEventListener('mousemove', function(e) {
            // 1. Ambil dimensi dan posisi section
            const rect = section.getBoundingClientRect();
            
            // 2. Hitung posisi mouse relatif terhadap section (dalam persen 0-100)
            const xPos = ((e.clientX - rect.left) / rect.width) * 100;
            const yPos = ((e.clientY - rect.top) / rect.height) * 100;

            // 3. Ubah titik tumpu (origin) gambar sesuai posisi mouse
            // Ini membuat efek "mengintip" atau mengikuti arah kursor
            bgImage.style.transformOrigin = `${xPos}% ${yPos}%`;
        });