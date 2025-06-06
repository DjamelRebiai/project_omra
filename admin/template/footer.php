        </div>
    </main>
    <script>
        // إظهار زر التبديل في الشاشات الصغيرة
        if (window.innerWidth <= 768) {
            document.querySelector('.toggle-sidebar').style.display = 'block';
        }
        
        // استجابة لتغيير حجم النافذة
        window.addEventListener('resize', function() {
            document.querySelector('.toggle-sidebar').style.display = 
                window.innerWidth <= 768 ? 'block' : 'none';
        });
    </script>
</body>
</html>
