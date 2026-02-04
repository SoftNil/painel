<?php
include 'topo.php';

// --- DADOS INICIAIS ---
$todas_tabelas = [];
$lista_tabelas_raw = mysqli_query($conecta, "SHOW TABLES");
$proximo_id = 0;

if ($lista_tabelas_raw) {
    while ($t = mysqli_fetch_array($lista_tabelas_raw)) {
        $tabela = $t[0];
        $visible = false;

        $cols_query = mysqli_query($conecta, "SHOW FULL COLUMNS FROM $tabela");
        if ($cols_query) {
            while ($c = mysqli_fetch_assoc($cols_query)) {
                if (stripos($c['Extra'], 'auto_increment') !== false) {
                    if (stripos($c['Comment'], 'visible=true') !== false) {
                        $visible = true;
                    }
                    break;
                }
            }
        }

        if ($visible) {
            $todas_tabelas[] = $tabela;
        }
    }
}

$all_tables_count_query = mysqli_query($conecta, "SHOW TABLES");
$proximo_id = mysqli_num_rows($all_tables_count_query) + 1;

$tabela_nome = isset($_GET['tabela']) ? $_GET['tabela'] : '';
$mode_new = isset($_GET['new']) && $_GET['new'] === 'true';

$colunas_banco = [];

if (!empty($tabela_nome)) {
    if (!$mode_new) {
        if (isset($conecta)) {
            $query = mysqli_query($conecta, "SHOW FULL COLUMNS FROM $tabela_nome");
            if ($query) {
                while ($row = mysqli_fetch_assoc($query)) {
                    $colunas_banco[] = $row;
                }
            } else {
                $erro = mysqli_error($conecta);
            }
        }
    }
}
?>
<style>
    .code-container {

        color: #d4d4d4;
        font-family: 'Consolas', monospace;
        font-size: 0.9rem;
        border-radius: 6px;
        padding: 1rem;
        max-height: 600px;
        overflow-y: auto;
    }

    .sql-keyword {
        color: #569cd6;
        font-weight: bold;
    }

    .sql-type {
        color: #4ec9b0;
    }

    .sql-string {
        color: #ce9178;
    }

    .column-deleted {
        opacity: 0.6;
        background-color: #f8d7da !important;
        border-color: #f5c6cb !important;
    }

    .dashboard-card {
        transition: transform 0.2s;
        cursor: pointer;
        border: 1px solid #dee2e6;
    }

    .dashboard-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .fk-section {

        border-radius: 5px;
        padding: 10px;
        margin-top: 10px;
    }

    .group-chip {
        display: inline-flex;
        align-items: center;
        background-color: #e9ecef;
        border-radius: 15px;
        padding: 5px 10px;
        margin: 2px;
        font-size: 0.85rem;
    }

    .group-chip-actions {
        margin-left: 8px;
        display: flex;
        gap: 5px;
    }

    .group-chip-actions i {
        cursor: pointer;
        opacity: 0.6;
        transition: opacity 0.2s;
    }

    .group-chip-actions i:hover {
        opacity: 1;
    }

    #btnAceita {
        padding-left: 200px;
    }
</style>

<div class="container">
 
    <?php if (empty($tabela_nome)): ?>
        <!-- ================= TELA 1: DASHBOARD ================= -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="alert alert-info">
                    <i class="ri-information-line"></i> Criando Tabela <strong><?php echo $proximo_id; ?></strong>.
                    <br>Somente tabelas com <code>visible=true</code> aparecem na lista.
                </div>

                <div class="card shadow mb-5">
                    <div class="card-body text-center py-5">
                        <h2 class="mb-3">Criar Nova Tabela</h2>
                         <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalTabela" title="Ativar e desativar edição Tabelas"><i class="ri-git-repository-private-fill"></i> Travamentos</button>
                        <div class="row g-3 mt-4">  
                            <div class="col-12">
                                <div class="border rounded p-4 h-100 dashboard-card" onclick="document.getElementById('newTableNameInput').focus()">
                                    <i class="ri-add-circle-line display-4 text-success d-block mb-3"></i>
                                    <div class="input-group mt-3">
                                        <span class="input-group-text">Nome Base</span>
                                        <input type="text" id="newTableNameInput" class="form-control" placeholder="ex: usuarios" onkeypress="handleEnterNewTable(event)">
                                        <button class="btn btn-success" onclick="goToNewTable()">Criar</button>
                                        
                                    </div>
                                    <small class="text-muted mt-2 d-block">Nome final: <strong id="previewName">usuarios_<?php echo $proximo_id; ?></strong></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Tabelas Disponíveis (visible=true)</div>
                    <div class="card-body">
                        <?php if (count($todas_tabelas) == 0): ?>
                            <div class="text-center text-muted py-3">Nenhuma tabela visível encontrada.</div>
                        <?php else: ?>
                            <select class="form-select" id="existingTableSelect" onchange="goToEditTable(this.value)">
                                <option value="" selected disabled>Selecione uma tabela...</option>
                                <?php foreach ($todas_tabelas as $t): ?>
                                    <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ================= TELA 2: EDITOR ================= -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ri-settings-4-line"></i>
                            <?php echo $mode_new ? 'Nova Tabela' : 'Editando: '; ?>
                            <strong><?php echo htmlspecialchars($tabela_nome); ?></strong>
                        </h5>
                        <a href="?" class="btn btn-outline-secondary btn-sm">
                            <i class="ri-arrow-go-back-line"></i> Voltar
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome da Tabela</label>
                            <input type="text" id="tableName" class="form-control" value="<?php echo htmlspecialchars($tabela_nome); ?>" readonly>
                        </div>
                        <div id="columnsContainer"></div>
                        <button class="btn btn-primary w-100 mt-3" id="btnAddColumn">
                            <i class="ri-add-circle-line"></i> Adicionar Coluna
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="ri-code-s-slash-line"></i> SQL</h5>
                        <span class="badge bg-secondary" id="modeBadge"><?php echo $mode_new ? 'CREATE' : 'ALTER'; ?></span>
                    </div>
                    <div class="card-body bg-secondary-subtle">
                        <div class="code-container" id="sqlOutput">-- Aguarde...</div>

                        <div class="mt-2">
                            <button class="btn btn-danger w-100 mb-1" id="btnExec" data-bs-toggle="modal" data-bs-target="#modalExecutarSQL" title="Executar SQL no Banco de Dados">
                                <i class="ri-play-circle-line"></i> Executar no Banco
                            </button>
                            <button class="btn btn-success w-100" title="Copiar SQL para memória" id="btnCopy"><i class="ri-file-copy-line"></i> Copiar SQL</button>
                            <div id="execFeedback" class="alert mt-2 p-1 small d-none text-center"></div>
                            <div class="toast-container position-relative mt-1 text-center w-100">
                                <div id="liveToast" class="toast align-items-center bg-success border-0 w-100" role="alert">
                                    <div class="d-flex">
                                        <div class="toast-body text-center w-100">SQL copiado!</div>
                                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL DE GERENCIAMENTO DE GRUPO -->
<div class="modal fade" id="modalTabela" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-primary">Configuração de Visibilidade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php include 'tabelas.php'; ?>
            </div>
            <div class="modal-footer justify-content-end">
                <button type="button" class="btn btn-danger me-2" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE GERENCIAMENTO DE GRUPO -->
<div class="modal fade" id="managerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-primary">Gerenciar Grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nome do Grupo (Linha na Tabela)</label>
                    <input type="text" id="groupNameInput" class="form-control" placeholder="Nome do Grupo">
                </div>

                <hr>

                <div class="input-group mb-3">
                    <input type="text" id="newItemInput" class="form-control" placeholder="Novo Item (ex: Quinto)">
                    <button class="btn btn-outline-secondary" type="button" onclick="addItemToGroup()">Adicionar</button>
                </div>

                <div id="groupItemsContainer" class="border p-2 rounded" style="min-height: 100px;">
                    <div class="text-center text-muted py-3">Carregando...</div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger d-none" id="btnDeleteGroup" onclick="deleteCurrentGroup()">
                    <i class="ri-delete-bin-line"></i> Excluir Grupo
                </button>
                <div>
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="saveGroupChanges()">
                        <i class="ri-save-line"></i> Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-alert py-5" id="modalExecutarSQL" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-body p-4 text-center">

                <h5 class="mb-0">Deseja mesmo executar o SQL?</h5>

                <p class="mb-0">A acão não podem desfeita. Os registros alterados não poderão ser recuperada.</p>
            </div>
            <div class="modal-footer flex-nowrap p-0">
                <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 ms-5 rounded-0 border-right" id="btnAceita" data-bs-dismiss="modal"><strong>Executar</strong></button>
                <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 me-5 rounded-0" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>



<?php include 'rodape.php'; ?>

<!-- Script do Dashboard -->
<script>
    const nextNum = <?php echo $proximo_id; ?>;

    document.getElementById('newTableNameInput').addEventListener('input', function(e) {
        const val = e.target.value.trim();
        if (val) document.getElementById('previewName').innerText = val + "_" + nextNum;
        else document.getElementById('previewName').innerText = "nome_" + nextNum;
    });

    function goToNewTable() {
        const input = document.getElementById('newTableNameInput');
        const nomeBase = input.value.trim();
        if (!nomeBase) return;
        const nomeFinal = nomeBase + "_" + nextNum;
        window.location.href = '?tabela=' + encodeURIComponent(nomeFinal) + '&new=true';
    }

    function handleEnterNewTable(e) {
        if (e.key === 'Enter') goToNewTable();
    }

    function goToEditTable(nome) {
        if (nome) window.location.href = '?tabela=' + encodeURIComponent(nome);
    }
</script>

<!-- Script do Editor -->
<?php if (!empty($tabela_nome)): ?>
    <script>
        const availableTables = <?php echo json_encode($todas_tabelas, JSON_UNESCAPED_UNICODE); ?>;

        function escapeHtml(text) {
            if (!text) return "";
            return text.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        const dbColumnsRaw = <?php echo json_encode($colunas_banco, JSON_UNESCAPED_UNICODE); ?>;
        const isNewTable = <?php echo $mode_new ? 'true' : 'false'; ?>;

        const columnTypes = {
            varchar: {
                needsLength: true,
                label: 'VARCHAR'
            },
            int: {
                needsLength: true,
                label: 'INT',
                defaultLength: 11
            },
            tinyint: {
                needsLength: true,
                label: 'TINYINT',
                defaultLength: 4
            },
            text: {
                label: 'TEXT'
            },
            boolean: {
                label: 'BOOLEAN'
            },
            date: {
                label: 'DATE'
            },
            time: {
                label: 'TIME'
            },
            datetime: {
                label: 'DATETIME'
            },
            enum: {
                needsValues: true,
                label: 'ENUM'
            },
            decimal: {
                needsPrecision: true,
                label: 'DECIMAL'
            }
        };

        let columns = [];
        let nextId = 1;
        let autoSuffix = "";

        // Variáveis para o Modal de Grupo
        let currentGroupOptions = [];
        let currentGroupOriginalName = "";
        let currentGroupColId = null;
        const managerModal = new bootstrap.Modal(document.getElementById('managerModal'));

        function parseFKComment(commentStr) {
            if (!commentStr || !commentStr.startsWith('fk:')) return null;
            const dataPart = commentStr.substring(3);
            const parts = dataPart.split('|');
            const result = {};
            parts.forEach(p => {
                const [key, val] = p.split('=');
                if (key && val) {
                    if (key === 'editavel' || key === 'grade' || key === 'form') {
                        result[key] = val.toLowerCase() === 'true';
                    } else {
                        result[key] = val;
                    }
                }
            });
            if (result.tabela && result.chave && result.campo) return result;
            return null;
        }

        function parseCMComment(commentStr) {
            if (!commentStr || !commentStr.startsWith('cm:')) return null;
            const dataPart = commentStr.substring(3);
            const parts = dataPart.split('|');
            const result = {};
            parts.forEach(p => {
                const [key, val] = p.split('=');
                if (key && val !== undefined) {
                    if (['editavel', 'grade', 'form'].includes(key)) {
                        result[key] = val.toLowerCase() === 'true';
                    } else {
                        result[key] = val;
                    }
                }
            });
            return result;
        }

        function parseMetaFromComment(comment, tableName) {
            let meta = {
                tabela: tableName,
                add: true,
                edit: true,
                delete: true,
                visible: true
            };
            const metaRegex = /tabela=([^\|]*)\|add=([^\|]*)\|edit=([^\|]*)\|delete=([^\|]*)\|visible=([^\|]*)/i;
            const match = comment.match(metaRegex);
            if (match) {
                meta.tabela = match[1];
                meta.add = match[2].toLowerCase() === 'true';
                meta.edit = match[3].toLowerCase() === 'true';
                meta.delete = match[4].toLowerCase() === 'true';
                meta.visible = match[5].toLowerCase() === 'true';
            }
            return {
                userComment: "",
                meta
            };
        }

        function parseMySQLType(fullType) {
            let type = fullType.toLowerCase();
            let length = '',
                values = '',
                precision = '';
            if (type.includes('(')) {
                const base = type.split('(')[0];
                const params = type.match(/\((.*)\)/)[1];
                if (base === 'enum' || base === 'set') {
                    type = 'enum';
                    values = params;
                } else if (base === 'decimal') {
                    type = 'decimal';
                    precision = params;
                } else if (base === 'tinyint') {
                    if (params === '1') type = 'boolean';
                    else {
                        type = 'tinyint';
                        length = params;
                    }
                } else {
                    type = base;
                    length = params;
                }
            } else {
                if (type === 'tinyint(1)' || type === 'tinyint') type = 'boolean';
                else if (!columnTypes[type]) type = 'varchar';
            }
            return {
                type,
                length,
                values,
                precision
            };
        }

        function initFromDB() {
            const tableNameInput = document.getElementById('tableName');
            if (!tableNameInput) {
                console.error("Erro: elemento tableName não encontrado");
                return;
            }

            const tableNameCurrent = tableNameInput.value;

            if (tableNameCurrent.includes('_')) {
                const parts = tableNameCurrent.split('_');
                autoSuffix = "_" + parts[parts.length - 1];
            }

            if (isNewTable) {
                columns = [{
                    id: nextId++,
                    name: 'id' + autoSuffix,
                    type: 'int',
                    length: '11',
                    nullable: false,
                    autoIncrement: true,
                    userComment: '',
                    comment: '',
                    default: '',
                    deleted: false,
                    isNew: true,
                    metaTable: tableNameCurrent,
                    metaAdd: true,
                    metaEdit: true,
                    metaDelete: true,
                    metaVisible: true,
                    relType: 'normal',
                    fkTable: '',
                    fkKey: '',
                    fkField: '',
                    fkLabel: '',
                    fkEdit: true,
                    fkGrid: true,
                    fkForm: true,
                    cmLabel: 'int',
                    cmEditavel: true,
                    cmGrade: true,
                    cmForm: true,
                    cmMascara: 'normal',
                    cmGrupo: 'nenhum'
                }];
            } else if (dbColumnsRaw && dbColumnsRaw.length > 0) {
                columns = dbColumnsRaw.map((col, index) => {
                    const parsed = parseMySQLType(col['Type']);
                    const isAuto = col['Extra'].toLowerCase().includes('auto_increment');

                    let fkData = {
                        relType: 'normal',
                        fkTable: '',
                        fkKey: '',
                        fkField: '',
                        fkLabel: '',
                        fkEdit: true,
                        fkGrid: true,
                        fkForm: true
                    };
                    let cmData = {
                        cmLabel: 'int',
                        cmEditavel: true,
                        cmGrade: true,
                        cmForm: true,
                        cmMascara: 'normal',
                        cmGrupo: 'nenhum'
                    };

                    if (!isAuto) {
                        const fkParsed = parseFKComment(col['Comment']);
                        if (fkParsed) {
                            fkData.relType = 'fk';
                            fkData.fkTable = fkParsed.tabela || '';
                            fkData.fkKey = fkParsed.chave || '';
                            fkData.fkField = fkParsed.campo || '';
                            fkData.fkLabel = fkParsed.label || '';
                            fkData.fkEdit = fkParsed.editavel !== undefined ? fkParsed.editavel : true;
                            fkData.fkGrid = fkParsed.grade !== undefined ? fkParsed.grade : true;
                            fkData.fkForm = fkParsed.form !== undefined ? fkParsed.form : true;
                        } else {
                            const cmParsed = parseCMComment(col['Comment']);
                            if (cmParsed) {
                                fkData.relType = 'cm';
                                cmData.cmLabel = cmParsed.label || 'int';
                                cmData.cmEditavel = cmParsed.editavel !== undefined ? cmParsed.editavel : true;
                                cmData.cmGrade = cmParsed.grade !== undefined ? cmParsed.grade : true;
                                cmData.cmForm = cmParsed.form !== undefined ? cmParsed.form : true;
                                cmData.cmMascara = cmParsed.mascara || 'normal';
                                cmData.cmGrupo = cmParsed.grupo || 'nenhum';
                            } else {
                                fkData.relType = 'normal';
                            }
                        }
                    }

                    const parsedMeta = parseMetaFromComment(col['Comment'], tableNameCurrent);

                    if (!columnTypes[parsed.type]) {
                        console.warn(`Tipo não suportado detectado: ${parsed.type}. Convertendo para VARCHAR.`);
                        parsed.type = 'varchar';
                        parsed.length = '255';
                    }

                    return {
                        id: nextId++,
                        originalName: col['Field'],
                        name: col['Field'],
                        type: parsed.type,
                        length: parsed.length,
                        values: parsed.values,
                        precision: parsed.precision,
                        nullable: col['Null'] === 'YES',
                        autoIncrement: isAuto,
                        default: col['Default'],
                        comment: col['Comment'],
                        userComment: isAuto ? "" : (fkData.relType === 'fk' ? "" : (fkData.relType === 'cm' ? "" : col['Comment'])),
                        deleted: false,
                        metaTable: parsedMeta.meta.tabela,
                        metaAdd: parsedMeta.meta.add,
                        metaEdit: parsedMeta.meta.edit,
                        metaDelete: parsedMeta.meta.delete,
                        metaVisible: parsedMeta.meta.visible,
                        ...fkData,
                        ...cmData
                    };
                });
            }
            renderColumns();
            generateSQL();
        }

        const columnsContainer = document.getElementById('columnsContainer');
        const sqlOutput = document.getElementById('sqlOutput');
        const btnExec = document.getElementById('btnExec');
        const btnAceita = document.getElementById('btnAceita');
        const btnCopy = document.getElementById('btnCopy');

        window.fetchColumnsForTable = async function(selectTableInput, colId, keyInput, fieldSelect) {
            const tableName = selectTableInput.value;
            if (!tableName) return;

            fieldSelect.innerHTML = '<option>Carregando...</option>';

            try {
                const response = await fetch(`?action=get_columns&table=${encodeURIComponent(tableName)}`);
                const data = await response.json();

                if (data.success) {
                    keyInput.value = data.autoInc || '';
                    fieldSelect.innerHTML = '';
                    data.columns.forEach(colName => {
                        const option = document.createElement('option');
                        option.value = colName;
                        option.textContent = colName;
                        fieldSelect.appendChild(option);
                    });

                    const colObj = columns.find(c => c.id === colId);
                    if (colObj && colObj.fkField) {
                        fieldSelect.value = colObj.fkField;
                    }

                    updateColumnFK(colId, 'fkTable', tableName);
                } else {
                    fieldSelect.innerHTML = '<option>Erro ao carregar</option>';
                }
            } catch (error) {
                console.error(error);
                fieldSelect.innerHTML = '<option>Erro de conexão</option>';
            }
        };

        window.fetchGruposForCM = async function(selectInput, colId) {
            if (selectInput.options.length > 2) return;

            try {
                const response = await fetch(`?action=get_grupos`);
                const data = await response.json();

                if (data.success) {
                    selectInput.innerHTML = '';
                    const noneOption = document.createElement('option');
                    noneOption.value = 'nenhum';
                    noneOption.textContent = 'nenhum';
                    selectInput.appendChild(noneOption);

                    const newOption = document.createElement('option');
                    newOption.value = '__new_group__';
                    newOption.textContent = '+ Adicionar Novo Grupo';
                    selectInput.appendChild(newOption);

                    data.grupos.forEach(grupoNome => {
                        const option = document.createElement('option');
                        option.value = grupoNome;
                        option.textContent = grupoNome;
                        selectInput.appendChild(option);
                    });

                    const colObj = columns.find(c => c.id === colId);
                    if (colObj && colObj.cmGrupo) {
                        selectInput.value = colObj.cmGrupo;
                    }
                }
            } catch (error) {
                console.error("Erro ao carregar grupos:", error);
            }
        };

        // --- LÓGICA DO MODAL DE GRUPO (CRUD LINHAS E ITENS) ---

        // Abre modal para Criar NOVO (Botão +)
        window.forceNewGroup = function(colId) {
            currentGroupColId = colId;
            document.getElementById('groupNameInput').value = '';
            document.getElementById('newItemInput').value = '';
            document.getElementById('groupItemsContainer').innerHTML = '';
            document.getElementById('btnDeleteGroup').classList.add('d-none');

            currentGroupOriginalName = "";
            currentGroupOptions = [];
            renderGroupChips();

            managerModal.show();
            document.getElementById('groupNameInput').focus();
        };

        // Abre modal para Editar/Ver (Select ou Botão Engrenagem)
        window.openGroupManager = async function(selectElement, colId) {
            const groupName = selectElement.value;
            currentGroupColId = colId;

            document.getElementById('groupNameInput').value = '';
            document.getElementById('newItemInput').value = '';
            document.getElementById('groupItemsContainer').innerHTML = '';
            document.getElementById('btnDeleteGroup').classList.add('d-none');

            managerModal.show();

            if (groupName === '__new_group__') {
                // Caso venha do select
                forceNewGroup(colId);
            } else if (groupName && groupName !== 'nenhum') {
                // Modo Editar Existente
                currentGroupOriginalName = groupName;
                document.getElementById('groupNameInput').value = groupName;
                document.getElementById('btnDeleteGroup').classList.remove('d-none');

                try {
                    const response = await fetch(`?action=get_group_details&name=${encodeURIComponent(groupName)}`);
                    const data = await response.json();

                    if (data.success) {
                        currentGroupOptions = data.options ? data.options.split('|').filter(item => item.trim() !== '') : [];
                        renderGroupChips();
                    } else {
                        currentGroupOptions = [];
                        renderGroupChips();
                    }
                } catch (error) {
                    console.error(error);
                    currentGroupOptions = [];
                    renderGroupChips();
                }
            } else {
                // 'nenhum' - Fecha o modal se for chamado por engano
                managerModal.hide();
                // alert("Selecione um grupo para gerenciar."); // Opcional
            }
        };

        function renderGroupChips() {
            const container = document.getElementById('groupItemsContainer');
            container.innerHTML = '';

            if (currentGroupOptions.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-2 small">Nenhum item. Adicione acima.</div>';
                return;
            }

            currentGroupOptions.forEach((item, index) => {
                const chip = document.createElement('div');
                chip.className = 'group-chip';
                chip.innerHTML = `
                <span>${escapeHtml(item)}</span>
                <div class="group-chip-actions">
                    <i class="ri-edit-line" title="Editar" onclick="editItem(${index})"></i>
                    <i class="ri-delete-bin-line text-danger" title="Deletar" onclick="deleteItem(${index})"></i>
                </div>
            `;
                container.appendChild(chip);
            });
        }

        window.addItemToGroup = function() {
            const input = document.getElementById('newItemInput');
            const val = input.value.trim();
            if (!val) return;

            currentGroupOptions.push(val);
            input.value = '';
            renderGroupChips();
        };

        document.getElementById('newItemInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') addItemToGroup();
        });

        window.editItem = function(index) {
            const currentVal = currentGroupOptions[index];
            const newVal = prompt("Editar item:", currentVal);

            if (newVal !== null && newVal.trim() !== '') {
                currentGroupOptions[index] = newVal.trim();
                renderGroupChips();
            }
        };

        window.deleteItem = function(index) {
            if (confirm("Tem certeza que deseja remover este item?")) {
                currentGroupOptions.splice(index, 1);
                renderGroupChips();
            }
        };

        window.saveGroupChanges = async function() {
            const newName = document.getElementById('groupNameInput').value.trim();
            const dataString = currentGroupOptions.join('|');

            if (!newName) {
                alert("O nome do grupo é obrigatório.");
                return;
            }

            const formData = new FormData();
            formData.append('action', 'save_group');
            formData.append('name', newName);
            formData.append('data', dataString);
            formData.append('original_name', currentGroupOriginalName);

            try {
                const response = await fetch('?', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    alert(result.message);

                    if (currentGroupColId) {
                        const col = columns.find(c => c.id === currentGroupColId);
                        if (col) {
                            col.cmGrupo = newName;
                            updateCM(currentGroupColId, 'cmGrupo', newName);

                            const grupoSel = document.getElementById(`cmGrupo_${currentGroupColId}`);
                            if (grupoSel) {
                                grupoSel.innerHTML = '<option value="nenhum">nenhum</option><option value="__new_group__">+ Adicionar Novo Grupo</option>';
                                fetchGruposForCM(grupoSel, currentGroupColId);
                            }
                        }
                    }

                    managerModal.hide();
                } else {
                    alert('Erro: ' + result.message);
                }
            } catch (error) {
                console.error(error);
                alert('Erro de conexão ao salvar.');
            }
        };

        window.deleteCurrentGroup = async function() {
            const groupName = currentGroupOriginalName;
            if (!groupName) return;

            if (!confirm(`Tem certeza que deseja excluir o grupo "${groupName}"? Isso apagará a linha da tabela.`)) return;

            const formData = new FormData();
            formData.append('action', 'delete_group');
            formData.append('name', groupName);

            try {
                const response = await fetch('?', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    alert(result.message);

                    if (currentGroupColId) {
                        const col = columns.find(c => c.id === currentGroupColId);
                        if (col) {
                            col.cmGrupo = 'nenhum';
                            updateCM(currentGroupColId, 'cmGrupo', 'nenhum');

                            const grupoSel = document.getElementById(`cmGrupo_${currentGroupColId}`);
                            if (grupoSel) {
                                grupoSel.innerHTML = '<option value="nenhum">nenhum</option><option value="__new_group__">+ Adicionar Novo Grupo</option>';
                                fetchGruposForCM(grupoSel, currentGroupColId);
                            }
                        }
                    }

                    managerModal.hide();
                } else {
                    alert('Erro: ' + result.message);
                }
            } catch (error) {
                console.error(error);
                alert('Erro de conexão ao deletar.');
            }
        };

        // -------------------------------

        window.updateColumnFK = function(id, field, val) {
            const col = columns.find(c => c.id === id);
            if (!col) return;

            if (field === 'relType') col.relType = val;
            else if (field === 'fkTable') col.fkTable = val;
            else if (field === 'fkKey') col.fkKey = val;
            else if (field === 'fkField') col.fkField = val;
            else if (field === 'fkLabel') col.fkLabel = val;
            else if (field === 'fkEdit') col.fkEdit = val;
            else if (field === 'fkGrid') col.fkGrid = val;
            else if (field === 'fkForm') col.fkForm = val;

            generateSQL();
            updateFKResultInput(id);
        };

        window.updateCM = function(id, field, val) {
            const col = columns.find(c => c.id === id);
            if (!col) return;

            col[field] = val;

            generateSQL();
            updateFKResultInput(id);
        };

        function updateFKResultInput(id) {
            const col = columns.find(c => c.id === id);
            const resultInput = document.getElementById(`fk_result_${id}`);
            if (!resultInput) return;

            let str = "";
            if (col.relType === 'fk') {
                str = `fk:tabela=${col.fkTable}|chave=${col.fkKey}|campo=${col.fkField}`;
                if (col.fkLabel) str += `|label=${col.fkLabel}`;
                str += `|editavel=${col.fkEdit}|grade=${col.fkGrid}|form=${col.fkForm}`;
            } else if (col.relType === 'cm') {
                str = `cm:label=${col.cmLabel}`;
                str += `|editavel=${col.cmEditavel}`;
                str += `|grade=${col.cmGrade}`;
                str += `|form=${col.cmForm}`;
                str += `|mascara=${col.cmMascara}`;
                str += `|grupo=${col.cmGrupo}`;
            } else {
                str = col.userComment || "";
            }

            resultInput.value = str;
        }

        function renderColumns() {
            if (!columnsContainer) return;
            columnsContainer.innerHTML = '';

            columns.forEach((col, index) => {
                if (!col.type) col.type = 'varchar';

                const safeName = escapeHtml(col.name);
                const safeLength = escapeHtml(col.length || col.values || col.precision || '');

                let metaInputsHTML = "";

                if (col.autoIncrement) {
                    const metaString = `tabela=${col.metaTable || ''}|add=${col.metaAdd}|edit=${col.metaEdit}|delete=${col.metaDelete}|visible=${col.metaVisible}`;

                    metaInputsHTML = `
                    <div class="row border p-2 mb-2 rounded">
                        <label class="col-12 small fw-bold text-primary mb-1"><i class="ri-shield-keyhole-line"></i> Configurações do Sistema (ID)</label>
                        <div class="col-md-12 mb-2">
                            <label class="form-label small fw-bold">Tabela</label>
                            <input type="text" class="form-control form-control-sm" value="${escapeHtml(col.metaTable || '')}" oninput="updateColumnMeta(${col.id}, 'metaTable', this.value)">
                        </div>
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="add_${col.id}" ${col.metaAdd ? 'checked' : ''} onchange="updateColumnMeta(${col.id}, 'metaAdd', this.checked)"><label class="form-check-label small" for="add_${col.id}">Add</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="edit_${col.id}" ${col.metaEdit ? 'checked' : ''} onchange="updateColumnMeta(${col.id}, 'metaEdit', this.checked)"><label class="form-check-label small" for="edit_${col.id}">Edit</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="delete_${col.id}" ${col.metaDelete ? 'checked' : ''} onchange="updateColumnMeta(${col.id}, 'metaDelete', this.checked)"><label class="form-check-label small" for="delete_${col.id}">Del</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="visible_${col.id}" ${col.metaVisible ? 'checked' : ''} onchange="updateColumnMeta(${col.id}, 'metaVisible', this.checked)"><label class="form-check-label small" for="visible_${col.id}">Vis</label></div>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <label class="form-label small fw-bold text-secondary">Comentário (Resultado)</label>
                            <input type="text" class="form-control form-control-sm font-monospace" readonly value="${escapeHtml(metaString)}" id="comment_result_${col.id}">
                        </div>
                    </div>
                `;
                } else {
                    const fkTableOptions = availableTables.map(t => `<option value="${t}" ${col.fkTable === t ? 'selected' : ''}>${t}</option>`).join('');

                    const fkSectionDisplay = (col.relType === 'fk') ? 'block' : 'none';
                    const normalCommentDisplay = (col.relType === 'normal') ? 'block' : 'none';
                    const cmCommentDisplay = (col.relType === 'cm') ? 'block' : 'none';

                    const maskOptions = ['normal', 'money', 'money2', 'CEP', 'CPF', 'CNPJ', 'data', 'data_hora']
                        .map(opt => `<option value="${opt}" ${col.cmMascara === opt ? 'selected' : ''}>${opt}</option>`)
                        .join('');

                    metaInputsHTML = `
                    <div class="row border p-2 mb-2 fk-section">
                        <label class="col-12 small fw-bold text-info mb-2"><i class="ri-links-line"></i> Tipo de Relacionamento</label>
                        
                        <div class="col-md-4 mb-2">
                            <select class="form-select form-select-sm" onchange="updateColumnFK(${col.id}, 'relType', this.value); renderColumns();">
                                <option value="normal" ${col.relType === 'normal' ? 'selected' : ''}>Normal</option>
                                <option value="fk" ${col.relType === 'fk' ? 'selected' : ''}>FK (Chave Estrangeira)</option>
                                <option value="cm" ${col.relType === 'cm' ? 'selected' : ''}>CM (Comentário Personalizado)</option>
                            </select>
                        </div>

                        <!-- Configuração FK -->
                        <div class="col-12 fk-config-area" style="display: ${fkSectionDisplay};">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="small fw-bold">Tabela (Destino)</label>
                                    <select class="form-select form-select-sm" id="fkTable_${col.id}" onchange="fetchColumnsForTable(this, ${col.id}, document.getElementById('fkKey_${col.id}'), document.getElementById('fkField_${col.id}'))">
                                        <option value="">Selecione...</option>
                                        ${fkTableOptions}
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">Chave (PK Auto)</label>
                                    <input type="text" class="form-control form-control-sm" id="fkKey_${col.id}" value="${escapeHtml(col.fkKey)}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">Campo (Display)</label>
                                    <select class="form-select form-select-sm" id="fkField_${col.id}" onchange="updateColumnFK(${col.id}, 'fkField', this.value)">
                                        <option value="">Selecione tabela...</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="small fw-bold text-primary">Label (Opcional - ex: SMTP)</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="Ex: SMTP" value="${escapeHtml(col.fkLabel)}" onchange="updateColumnFK(${col.id}, 'fkLabel', this.value)">
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="small fw-bold">Visibilidade</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="fkEdit_${col.id}" ${col.fkEdit ? 'checked' : ''} onchange="updateColumnFK(${col.id}, 'fkEdit', this.checked)"><label class="form-check-label small" for="fkEdit_${col.id}">Editável</label></div>
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="fkGrid_${col.id}" ${col.fkGrid ? 'checked' : ''} onchange="updateColumnFK(${col.id}, 'fkGrid', this.checked)"><label class="form-check-label small" for="fkGrid_${col.id}">Grade</label></div>
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="fkForm_${col.id}" ${col.fkForm ? 'checked' : ''} onchange="updateColumnFK(${col.id}, 'fkForm', this.checked)"><label class="form-check-label small" for="fkForm_${col.id}">Formulário</label></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuração CM -->
                        <div class="col-12 cm-config-area" style="display: ${cmCommentDisplay}; border-top: 1px solid #eee; padding-top: 10px;">
                            <label class="col-12 small fw-bold text-primary mb-2"><i class="ri-settings-3-line"></i> Configurações CM</label>
                            
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="small fw-bold">Label</label>
                                    <input type="text" class="form-control form-control-sm" 
                                        value="${escapeHtml(col.cmLabel)}" 
                                        oninput="updateCM(${col.id}, 'cmLabel', this.value)">
                                </div>

                                <div class="col-md-6">
                                    <label class="small fw-bold">Máscara</label>
                                    <select class="form-select form-select-sm" onchange="updateCM(${col.id}, 'cmMascara', this.value)">
                                        ${maskOptions}
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="small fw-bold">Grupo</label>
                                    <div class="input-group input-group-sm">
                                        <select class="form-select" id="cmGrupo_${col.id}" onchange="updateCM(${col.id}, 'cmGrupo', this.value)">
                                            <option value="nenhum">nenhum</option>
                                            <option value="__new_group__">+ Adicionar Novo Grupo</option>
                                        </select>
                                        <!-- BOTÃO NOVO: Adicionar Grupo -->
                                        <button class="btn btn-outline-success" type="button" title="Adicionar Novo Grupo" onclick="forceNewGroup(${col.id})">
                                            <i class="ri-add-line"></i>
                                        </button>
                                        <!-- BOTÃO EXISTENTE: Gerenciar Grupo Selecionado -->
                                        <button class="btn btn-outline-secondary" type="button" title="Gerenciar Grupo Selecionado" onclick="openGroupManager(document.getElementById('cmGrupo_${col.id}'), ${col.id})">
                                            <i class="ri-settings-4-line"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12 mt-2">
                                    <label class="small fw-bold">Propriedades</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="cmEdit_${col.id}" ${col.cmEditavel ? 'checked' : ''} onchange="updateCM(${col.id}, 'cmEditavel', this.checked)">
                                            <label class="form-check-label small" for="cmEdit_${col.id}">Editável</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="cmGrade_${col.id}" ${col.cmGrade ? 'checked' : ''} onchange="updateCM(${col.id}, 'cmGrade', this.checked)">
                                            <label class="form-check-label small" for="cmGrade_${col.id}">Grade</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="cmForm_${col.id}" ${col.cmForm ? 'checked' : ''} onchange="updateCM(${col.id}, 'cmForm', this.checked)">
                                            <label class="form-check-label small" for="cmForm_${col.id}">Form</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuração Normal -->
                        <div class="col-12" style="display: ${normalCommentDisplay};">
                            <label class="small fw-bold">Comentário (Texto Livre)</label>
                            <input type="text" class="form-control form-control-sm" value="${escapeHtml(col.userComment)}" oninput="updateColumn(${col.id}, 'userComment', this.value); updateFKResultInput(${col.id});">
                        </div>

                        <!-- Input de Resultado -->
                        <div class="col-12 mt-2">
                            <label class="small fw-bold text-secondary"><i class="ri-code-s-slash-line"></i> Resultado (Comentário SQL)</label>
                            <input type="text" class="form-control form-control-sm font-monospace" readonly id="fk_result_${col.id}">
                        </div>
                    </div>
                `;

                    setTimeout(() => {
                        if (col.relType === 'fk' && col.fkTable) {
                            const tableSel = document.getElementById(`fkTable_${col.id}`);
                            const keyInp = document.getElementById(`fkKey_${col.id}`);
                            const fieldSel = document.getElementById(`fkField_${col.id}`);
                            if (tableSel && keyInp && fieldSel) {
                                fetchColumnsForTable(tableSel, col.id, keyInp, fieldSel);
                            }
                        }
                        if (col.relType === 'cm') {
                            const grupoSel = document.getElementById(`cmGrupo_${col.id}`);
                            if (grupoSel) {
                                fetchGruposForCM(grupoSel, col.id);
                            }
                        }
                    }, 100);
                }

                const div = document.createElement('div');
                div.className = `card mb-3 border ${col.deleted ? 'border-danger column-deleted' : ''}`;
                div.innerHTML = `
                <div class="card-header py-2 d-flex justify-content-between align-items-center ${col.deleted ? 'bg-danger-subtle' : ''}">
                    <strong class="text-secondary">${col.deleted ? '<i class="ri-delete-bin-line"></i> REMOVIDO' : 'Campo #' + (index +1)}</strong>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary" onclick="moveColumn(${col.id}, -1)" ${index === 0 ? 'disabled' : ''}><i class="ri-arrow-up-line"></i></button>
                        <button class="btn btn-outline-secondary" onclick="moveColumn(${col.id}, 1)" ${index === columns.length - 1 ? 'disabled' : ''}><i class="ri-arrow-down-line"></i></button>
                        <button class="btn ${col.deleted ? 'btn-success' : 'btn-outline-danger'}" onclick="toggleDelete(${col.id})"><i class="ri ${col.deleted ? 'ri-arrow-go-back-line' : 'ri-delete-bin-line'}"></i></button>
                    </div>
                </div>
                <div class="card-body" style="${col.deleted ? 'opacity:0.5; pointer-events:none;' : ''}">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Nome</label>
                            <input type="text" class="form-control form-control-sm" value="${safeName}" oninput="updateColumn(${col.id}, 'name', this.value)">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Tipo</label>
                            <select class="form-select form-select-sm" onchange="updateColumn(${col.id}, 'type', this.value)">
                                ${Object.keys(columnTypes).map(k => `<option value="${k}" ${col.type === k ? 'selected' : ''}>${columnTypes[k].label}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">${getDynamicLabel(col.type)}</label>
                            <input type="text" class="form-control form-control-sm" value="${safeLength}" oninput="updateDynamicParam(${col.id}, this.value)" placeholder="${getDynamicPlaceholder(col.type)}">
                        </div>
                        <div class="col-6 col-md-3 mt-2">
                            <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="null_${col.id}" ${col.nullable ? 'checked' : ''} onchange="updateColumn(${col.id}, 'nullable', this.checked)"><label class="form-check-label small" for="null_${col.id}">NULL</label></div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="ai_${col.id}" ${col.autoIncrement ? 'checked' : ''} onchange="updateColumn(${col.id}, 'autoIncrement', this.checked)"><label class="form-check-label small" for="ai_${col.id}">AI</label></div>
                        </div>
                        <div class="col-6 col-md-3 mt-2">
                            <label class="form-label small fw-bold">Default</label>
                            <input type="text" class="form-control form-control-sm" value="${escapeHtml(col.default || '')}" oninput="updateColumn(${col.id}, 'default', this.value)" placeholder="NULL">
                        </div>
                        
                        <!-- SEÇÃO DE METADADOS / FK / CM -->
                        ${metaInputsHTML}
                    </div>
                </div>
            `;
                columnsContainer.appendChild(div);

                updateFKResultInput(col.id);
            });
        }

        function addColumn() {
            let novoNome = "coluna" + autoSuffix;
            columns.push({
                id: nextId++,
                name: novoNome,
                type: 'varchar',
                length: '255',
                nullable: true,
                autoIncrement: false,
                userComment: '',
                comment: '',
                default: '',
                deleted: false,
                isNew: true,
                metaTable: '',
                metaAdd: true,
                metaEdit: true,
                metaDelete: true,
                metaVisible: true,
                relType: 'normal',
                fkTable: '',
                fkKey: '',
                fkField: '',
                fkLabel: '',
                fkEdit: true,
                fkGrid: true,
                fkForm: true,
                cmComment: '',
                cmLabel: 'int',
                cmEditavel: true,
                cmGrade: true,
                cmForm: true,
                cmMascara: 'normal',
                cmGrupo: 'nenhum'
            });
            renderColumns();
            generateSQL();
        }

        window.moveColumn = function(id, dir) {
            const idx = columns.findIndex(c => c.id === id);
            if (idx === -1) return;
            const newIdx = idx + dir;
            if (newIdx < 0 || newIdx >= columns.length) return;
            [columns[idx], columns[newIdx]] = [columns[newIdx], columns[idx]];
            renderColumns();
            generateSQL();
        };
        window.toggleDelete = function(id) {
            const col = columns.find(c => c.id === id);
            if (col) {
                col.deleted = !col.deleted;
                renderColumns();
                generateSQL();
            }
        }
        window.updateColumn = function(id, field, val) {
            const col = columns.find(c => c.id === id);
            if (col) {
                col[field] = val;
                if (field === 'type') {
                    col.length = '';
                    col.values = '';
                    col.precision = '';
                    renderColumns();
                }
                if (field === 'autoIncrement' && val === true && !col.metaTable) {
                    col.metaTable = document.getElementById('tableName').value;
                }
                generateSQL();
                if (!col.autoIncrement) updateFKResultInput(id);
            }
        };

        window.updateColumnMeta = function(id, field, val) {
            const col = columns.find(c => c.id === id);
            if (col) {
                col[field] = val;
                generateSQL();

                if (col.autoIncrement) {
                    const resultInput = document.getElementById('comment_result_' + id);
                    if (resultInput) {
                        const metaString = `tabela=${col.metaTable || ''}|add=${col.metaAdd}|edit=${col.metaEdit}|delete=${col.metaDelete}|visible=${col.metaVisible}`;
                        resultInput.value = metaString;
                    }
                }
            }
        };

        window.updateDynamicParam = function(id, val) {
            const col = columns.find(c => c.id === id);
            if (!col) return;
            if (col.type === 'enum') col.values = val;
            else if (col.type === 'decimal') col.precision = val;
            else col.length = val;
            generateSQL();
        };

        function getDynamicLabel(t) {
            if (t === 'enum') return 'Valores';
            if (t === 'decimal') return 'Precisão';
            if (['varchar', 'int', 'tinyint'].includes(t)) return 'Tamanho';
            return 'Parâm.';
        }

        function getDynamicPlaceholder(t) {
            if (t === 'enum') return "'op1','op2'";
            if (t === 'decimal') return '10,2';
            if (t === 'int') return '11';
            if (t === 'varchar') return '255';
            if (t === 'tinyint') return '4';
            return '';
        }

        let currentRawSQL = "";

        function generateSQL() {
            if (!sqlOutput) return;
            try {
                const tableNameInput = document.getElementById('tableName');
                if (!tableNameInput) return;
                const tableName = tableNameInput.value.trim();
                if (!tableName) return;

                let sql = "";

                if (isNewTable) {
                    let lines = [];
                    columns.forEach(col => {
                        if (col.deleted) return;
                        lines.push(buildColumnDefinition(col, tableName));
                    });
                    const pkCol = columns.find(c => c.autoIncrement && !c.deleted);
                    if (pkCol) lines.push(`PRIMARY KEY (\`${pkCol.name}\`)`);

                    sql = `CREATE TABLE \`${tableName}\` (\n  ${lines.join(',\n  ')}\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;`;
                } else {
                    let drops = [],
                        alters = [];
                    columns.forEach(col => {
                        if (col.deleted && !col.isNew) drops.push(`ALTER TABLE \`${tableName}\` DROP COLUMN \`${col.originalName}\`;`);
                    });

                    columns.forEach((col, idx) => {
                        if (col.deleted) return;
                        const colDef = buildColumnDefinition(col, tableName);
                        const after = idx > 0 ? `AFTER \`${columns[idx-1].name}\`` : 'FIRST';
                        let q = '';
                        if (col.isNew) q = `ALTER TABLE \`${tableName}\` ADD COLUMN ${colDef} ${after};`;
                        else {
                            if (col.name !== col.originalName) {
                                q = `ALTER TABLE \`${tableName}\` CHANGE COLUMN \`${col.originalName}\` ${colDef} ${after};`;
                                col.originalName = col.name;
                            } else q = `ALTER TABLE \`${tableName}\` MODIFY COLUMN ${colDef} ${after};`;
                        }
                        alters.push(q);
                    });
                    sql = [...drops, ...alters].join('\n');
                }

                currentRawSQL = sql;
                sqlOutput.innerHTML = syntaxHighlight(sql);
                btnExec.disabled = false;
            } catch (e) {
                console.error("Erro ao gerar SQL:", e);
                sqlOutput.innerHTML = `<span style="color:red">Erro ao gerar SQL: ${escapeHtml(e.message)}</span>`;
                btnExec.disabled = true;
            }
        }

        function buildColumnDefinition(c, tableName) {
            if (!columnTypes[c.type]) {
                console.warn(`Coluna ${c.name} com tipo desconhecido: ${c.type}. Usando VARCHAR.`);
                c.type = 'varchar';
            }

            let d = `\`${c.name}\` `;
            let t = columnTypes[c.type].label;

            if (c.type === 'enum' && c.values) t += `(${c.values})`;
            else if (c.type === 'decimal' && c.precision) t += `(${c.precision})`;
            else if (c.type === 'varchar' && c.length) t += `(${c.length})`;
            else if (c.type === 'int' && c.length) t += `(${c.length})`;
            else if (c.type === 'tinyint' && c.length) t += `(${c.length})`;

            d += t + (c.nullable ? ' NULL' : ' NOT NULL');
            if (c.autoIncrement) d += ' AUTO_INCREMENT';

            let finalComment = "";

            if (c.autoIncrement) {
                const metaStr = `tabela=${c.metaTable || tableName}|add=${c.metaAdd}|edit=${c.metaEdit}|delete=${c.metaDelete}|visible=${c.metaVisible}`;
                finalComment = metaStr;
            } else if (c.relType === 'fk') {
                let fkStr = `fk:tabela=${c.fkTable}|chave=${c.fkKey}|campo=${c.fkField}`;
                if (c.fkLabel) fkStr += `|label=${c.fkLabel}`;
                fkStr += `|editavel=${c.fkEdit}|grade=${c.fkGrid}|form=${c.fkForm}`;
                finalComment = fkStr;
            } else if (c.relType === 'cm') {
                let cmStr = `cm:label=${c.cmLabel}`;
                cmStr += `|editavel=${c.cmEditavel}`;
                cmStr += `|grade=${c.cmGrade}`;
                cmStr += `|form=${c.cmForm}`;
                cmStr += `|mascara=${c.cmMascara}`;
                cmStr += `|grupo=${c.cmGrupo}`;
                finalComment = cmStr;
            } else {
                finalComment = c.userComment || "";
            }

            if (finalComment) d += ` COMMENT '${finalComment.replace(/'/g, "''")}'`;

            if (c.default) {
                const defUpper = c.default.toUpperCase();
                if (defUpper === 'NULL') d += ' DEFAULT NULL';
                else if (defUpper === 'CURRENT_TIMESTAMP') d += ' DEFAULT CURRENT_TIMESTAMP';
                else if (!isNaN(c.default) && c.type !== 'varchar' && c.type !== 'enum' && c.type !== 'date' && c.type !== 'time' && c.type !== 'datetime' && c.type !== 'tinyint') d += ` DEFAULT ${c.default}`;
                else d += ` DEFAULT '${c.default.replace(/'/g, "''")}'`;
            }
            return d;
        }

        function syntaxHighlight(sql) {
            if (!sql) return '';
            sql = sql.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return sql.replace(/\b(CREATE|TABLE|ALTER|DROP|COLUMN|ADD|MODIFY|CHANGE|FIRST|AFTER|NULL|NOT|DEFAULT|AUTO_INCREMENT|COMMENT|KEY|PRIMARY|ENGINE|CHARSET)\b/gi, '<span class="sql-keyword">$1</span>')
                .replace(/\b(INT|VARCHAR|TEXT|BOOLEAN|DATE|TIME|DATETIME|ENUM|DECIMAL|TINYINT|InnoDB|utf8mb4)\b/gi, '<span class="sql-type">$1</span>')
                .replace(/`([^`]+)`/g, '<span class="sql-string">`$1`</span>')
                .replace(/'([^']+)'/g, '<span class="sql-string">\'$1\'</span>');
        }

        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById('btnAddColumn').addEventListener('click', addColumn);
            const toast = new bootstrap.Toast(document.getElementById('liveToast'));

            document.getElementById('btnCopy').addEventListener('click', () => {
                // 1. Pega o texto puro da variável global que armazena o SQL (sem cores HTML)
                if (!currentRawSQL) {
                    alert("Nenhum SQL gerado para copiar.");
                    return;
                }

                // 2. Tenta copiar usando a API moderna do Clipboard
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(currentRawSQL)
                        .then(() => {
                            // Mostra o Toast de sucesso
                            const toast = new bootstrap.Toast(document.getElementById('liveToast'));
                            toast.show();
                        })
                        .catch(err => {
                            console.error('Erro ao copiar: ', err);
                            alert("Erro automático. Tente manualmente (Ctrl+C).");
                        });
                } else {
                    // 3. Fallback para navegadores antigos (Edge antigo, Safari antigo, etc)
                    const textArea = document.createElement("textarea");
                    textArea.value = currentRawSQL;

                    // Move para fora da tela para não atrapalhar
                    textArea.style.position = "fixed";
                    textArea.style.left = "-9999px";
                    document.body.appendChild(textArea);

                    textArea.focus();
                    textArea.select();

                    try {
                        const successful = document.execCommand('copy');
                        if (successful) {
                            const toast = new bootstrap.Toast(document.getElementById('liveToast'));
                            toast.show();
                        } else {
                            alert("Não foi possível copiar.");
                        }
                    } catch (err) {
                        console.error('Fallback: Oops, unable to copy', err);
                        alert("Não foi possível copiar automaticamente.");
                    }

                    document.body.removeChild(textArea);
                }
            });

            btnAceita.addEventListener('click', function() {
                if (!currentRawSQL) return;

                btnExec.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Executando...';
                btnExec.disabled = true;

                const formData = new FormData();
                formData.append('action', 'exec_sql');
                formData.append('sql', currentRawSQL);

                fetch('?', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        const feedback = document.getElementById('execFeedback');
                        feedback.classList.remove('d-none', 'alert-danger', 'alert-success');

                        if (data.success) {
                            feedback.classList.add('alert', 'alert-success');
                            feedback.innerText = data.message;
                            setTimeout(() => {
                                window.location.href = "?tabela=" + encodeURIComponent(document.getElementById('tableName').value);
                            }, 1500);
                        } else {
                            feedback.classList.add('alert', 'alert-danger');
                            feedback.innerText = "Erro: " + data.message;
                            btnExec.innerHTML = '<i class="ri-play-circle-line"></i> Executar no Banco';
                            btnExec.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        const feedback = document.getElementById('execFeedback');
                        feedback.classList.remove('d-none');
                        feedback.classList.add('alert', 'alert-danger');
                        feedback.innerText = "Erro de comunicação.";
                        btnExec.innerHTML = '<i class="ri-play-circle-line"></i> Executar no Banco';
                        btnExec.disabled = false;
                    });
            });

            initFromDB();
        });
    </script>
<?php endif; ?>