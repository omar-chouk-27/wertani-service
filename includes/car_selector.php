<!-- 
    CAR SELECTOR COMPONENT
    Usage: include this file where you need car selection
    
    Variables needed before including:
    - $selected_car_id (optional): ID of currently selected car
    - $field_name (optional): name attribute for select field (default: 'car_id')
    - $required (optional): whether field is required (default: false)
-->

<?php
// Load all active cars
$all_cars = getData($conn, "SELECT * FROM cars WHERE IsActive = 1 ORDER BY Marque, Modele");

// Set defaults
$field_name = $field_name ?? 'car_id';
$required = $required ?? false;
$selected_car_id = $selected_car_id ?? '';
?>

<div class="car-selector-container" style="position: relative;">
    <div style="display: flex; gap: 10px; align-items: flex-start;">
        <div style="flex: 1;">
            <select name="<?php echo $field_name; ?>" 
                    id="car_select" 
                    class="form-select"
                    <?php echo $required ? 'required' : ''; ?>>
                <option value="">-- Sélectionner une voiture --</option>
                <?php 
                $current_marque = '';
                foreach ($all_cars as $car): 
                    // Group by marque
                    if ($car['Marque'] !== $current_marque) {
                        if ($current_marque !== '') echo '</optgroup>';
                        echo '<optgroup label="' . e($car['Marque']) . '">';
                        $current_marque = $car['Marque'];
                    }
                    
                    $car_label = $car['Modele'];
                    if ($car['Annee']) {
                        $car_label .= ' (' . $car['Annee'] . ')';
                    }
                    $car_label .= ' - ' . $car['Type'];
                    
                    $is_selected = ($selected_car_id == $car['Id']) ? 'selected' : '';
                ?>
                    <option value="<?php echo $car['Id']; ?>" <?php echo $is_selected; ?>>
                        <?php echo e($car_label); ?>
                    </option>
                <?php endforeach; ?>
                <?php if ($current_marque !== '') echo '</optgroup>'; ?>
            </select>
            <small style="color: #666; display: block; margin-top: 5px;">
                💡 Si la voiture n'existe pas, cliquez sur "Ajouter Nouvelle Voiture"
            </small>
        </div>
        
        <button type="button" 
                class="btn btn-success" 
                onclick="openCarModal()"
                style="white-space: nowrap;">
            ➕ Nouvelle Voiture
        </button>
    </div>
</div>

<!-- MODAL FOR ADDING NEW CAR -->
<div id="carModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>➕ Ajouter une Nouvelle Voiture</h3>
            <button type="button" class="close-btn" onclick="closeCarModal()">&times;</button>
        </div>
        
        <form id="addCarForm" onsubmit="return addNewCar(event)">
            <table class="form-table">
                <tr>
                    <td class="required">Marque</td>
                    <td>
                        <input type="text" 
                               id="new_car_marque" 
                               class="form-control" 
                               placeholder="Ex: Toyota, Nissan, Ford..."
                               required>
                    </td>
                </tr>
                <tr>
                    <td class="required">Modèle</td>
                    <td>
                        <input type="text" 
                               id="new_car_modele" 
                               class="form-control" 
                               placeholder="Ex: Hilux, Patrol, Ranger..."
                               required>
                    </td>
                </tr>
                <tr>
                    <td>Année</td>
                    <td>
                        <input type="number" 
                               id="new_car_annee" 
                               class="form-control" 
                               value="<?php echo date('Y'); ?>"
                               min="1990" 
                               max="<?php echo date('Y') + 1; ?>">
                    </td>
                </tr>
                <tr>
                    <td>Type</td>
                    <td>
                        <select id="new_car_type" class="form-select">
                            <option value="4x4">4x4</option>
                            <option value="SUV">SUV</option>
                            <option value="Pickup">Pickup</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <div style="padding: 1rem; text-align: right; background: #f8f9fa; border-top: 1px solid #ddd;">
                <button type="button" class="btn btn-secondary" onclick="closeCarModal()">
                    Annuler
                </button>
                <button type="submit" class="btn btn-success">
                    ➕ Ajouter et Sélectionner
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 2px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
}

.modal-header h3 {
    margin: 0;
    color: #333;
}

.close-btn {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-btn:hover {
    color: #000;
}
</style>

<script>
function openCarModal() {
    document.getElementById('carModal').style.display = 'flex';
}

function closeCarModal() {
    document.getElementById('carModal').style.display = 'none';
    document.getElementById('addCarForm').reset();
}

function addNewCar(event) {
    event.preventDefault();
    
    const marque = document.getElementById('new_car_marque').value;
    const modele = document.getElementById('new_car_modele').value;
    const annee = document.getElementById('new_car_annee').value;
    const type = document.getElementById('new_car_type').value;
    
    // Create FormData
    const formData = new FormData();
    formData.append('action', 'add_car_ajax');
    formData.append('marque', marque);
    formData.append('modele', modele);
    formData.append('annee', annee);
    formData.append('type', type);
    
    // Send AJAX request
    fetch('cars_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add new option to select
            const select = document.getElementById('car_select');
            
            // Find or create optgroup for this marque
            let optgroup = null;
            const optgroups = select.getElementsByTagName('optgroup');
            for (let i = 0; i < optgroups.length; i++) {
                if (optgroups[i].label === marque) {
                    optgroup = optgroups[i];
                    break;
                }
            }
            
            if (!optgroup) {
                optgroup = document.createElement('optgroup');
                optgroup.label = marque;
                select.appendChild(optgroup);
            }
            
            // Create new option
            const option = document.createElement('option');
            option.value = data.car_id;
            option.text = modele + (annee ? ' (' + annee + ')' : '') + ' - ' + type;
            option.selected = true;
            optgroup.appendChild(option);
            
            // Close modal
            closeCarModal();
            
            // Show success message
            alert('✅ Voiture ajoutée avec succès!');
        } else {
            alert('❌ Erreur: ' + (data.message || 'Impossible d\'ajouter la voiture'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Erreur lors de l\'ajout de la voiture');
    });
    
    return false;
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('carModal');
    if (event.target === modal) {
        closeCarModal();
    }
});
</script>
