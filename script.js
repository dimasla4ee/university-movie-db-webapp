// Хранилище для подсказок актёров (заполняется из PHP)
let actorSuggestions = [];

// Счётчики для уникальных индексов
let actorCounter = 1;
let factCounter = 1;

// Функция добавления новой строки
function addRow(type) {
    const container = document.getElementById(type === 'actor' ? 'actorsContainer' : 'factsContainer');
    const newRow = document.createElement('div');
    newRow.className = 'row';
    newRow.setAttribute('data-type', type);
    
    const index = type === 'actor' ? actorCounter++ : factCounter++;
    newRow.setAttribute('data-index', index);
    
    if (type === 'actor') {
        newRow.innerHTML = `
            <input type="text" class="actor-input" name="actors[]" placeholder="Введите имя актёра" autocomplete="off" style="width: 300px;">
            <button type="button" class="remove-btn" onclick="removeRow(this)">✖</button>
            <div class="suggestions" style="position: absolute; background: white; border: 1px solid #ccc; display: none;"></div>
        `;
        container.appendChild(newRow);
        
        // Настраиваем автодополнение для нового поля
        const input = newRow.querySelector('.actor-input');
        setupAutocomplete(input);
    } else {
        newRow.innerHTML = `
            <input type="text" class="fact-input" name="fun_facts[]" placeholder="Введите интересный факт" style="width: 400px;">
            <button type="button" class="remove-btn" onclick="removeRow(this)">✖</button>
        `;
        container.appendChild(newRow);
    }
}

// Функция удаления строки
function removeRow(button) {
    const row = button.parentElement;
    const type = row.getAttribute('data-type');
    const container = document.getElementById(type === 'actor' ? 'actorsContainer' : 'factsContainer');
    
    if (container.children.length > 1) {
        row.remove();
    } else {
        alert('Должна быть хотя бы одна запись');
    }
}

// Функция автодополнения для актёров
function setupAutocomplete(input) {
    let suggestionsDiv = document.createElement('div');
    suggestionsDiv.className = 'suggestions';
    suggestionsDiv.style.position = 'absolute';
    suggestionsDiv.style.backgroundColor = 'white';
    suggestionsDiv.style.border = '1px solid #ccc';
    suggestionsDiv.style.maxHeight = '150px';
    suggestionsDiv.style.overflowY = 'auto';
    suggestionsDiv.style.zIndex = '1000';
    suggestionsDiv.style.display = 'none';
    input.parentNode.style.position = 'relative';
    input.parentNode.appendChild(suggestionsDiv);
    
    input.addEventListener('input', function() {
        const value = this.value.toLowerCase();
        suggestionsDiv.innerHTML = '';
        
        if (value.length === 0) {
            suggestionsDiv.style.display = 'none';
            return;
        }
        
        const matches = actorSuggestions.filter(name =>
            name.toLowerCase().includes(value)
        );
        
        if (matches.length > 0) {
            matches.forEach(match => {
                const div = document.createElement('div');
                div.textContent = match;
                div.style.padding = '5px';
                div.style.cursor = 'pointer';
                div.addEventListener('click', function() {
                    input.value = match;
                    suggestionsDiv.style.display = 'none';
                });
                div.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f0f0f0';
                });
                div.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = 'white';
                });
                suggestionsDiv.appendChild(div);
            });
            suggestionsDiv.style.display = 'block';
            
            // Позиционирование подсказок
            const rect = input.getBoundingClientRect();
            suggestionsDiv.style.top = (rect.bottom + window.scrollY) + 'px';
            suggestionsDiv.style.left = (rect.left + window.scrollX) + 'px';
            suggestionsDiv.style.width = rect.width + 'px';
        } else {
            suggestionsDiv.style.display = 'none';
        }
    });
    
    input.addEventListener('blur', function() {
        setTimeout(() => {
            suggestionsDiv.style.display = 'none';
        }, 200);
    });
}

// Добавление выбранного актёра из выпадающего списка
function addExistingActor() {
    const select = document.getElementById('existingActorSelect');
    const selectedName = select.value;
    
    if (selectedName === '') {
        alert('Выберите актёра из списка');
        return;
    }
    
    // Проверяем, не добавлен ли уже этот актёр
    const inputs = document.querySelectorAll('.actor-input');
    let alreadyExists = false;
    inputs.forEach(input => {
        if (input.value === selectedName) {
            alreadyExists = true;
        }
    });
    
    if (alreadyExists) {
        alert('Этот актёр уже добавлен');
        return;
    }
    
    // Находим первое пустое поле или создаём новое
    let targetInput = null;
    for (let input of inputs) {
        if (input.value === '') {
            targetInput = input;
            break;
        }
    }
    
    if (!targetInput) {
        addRow('actor');
        const newInput = document.querySelector('#actorsContainer .row:last-child .actor-input');
        newInput.value = selectedName;
    } else {
        targetInput.value = selectedName;
    }
}

// Настройка автодополнения для существующих полей при загрузке
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.actor-input');
    inputs.forEach(input => setupAutocomplete(input));
});