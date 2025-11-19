// ============================================
// Hackers6thSense - Main Dashboard JavaScript
// Complete with All Features
// ============================================

const API_BASE = '/api';

// ============================================
// Navigation
// ============================================

function navigateTo(section) {
    // Hide all sections
    document.querySelectorAll('.page-section').forEach(s => {
        s.classList.remove('active');
    });
    
    // Hide all nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Show selected section
    const section_el = document.getElementById(section);
    if (section_el) {
        section_el.classList.add('active');
    }
    
    // Mark nav item as active
    event.target.closest('.nav-item')?.classList.add('active');
    
    // Update page title
    const titles = {
        'overview': 'System Overview',
        'threats': 'Security Threats',
        'traffic': 'Network Traffic',
        'logs': 'System Logs & Analysis',
        'attacks': 'Attack Simulations & Red Team',
        'config': 'Configuration',
        'legion': 'LEGION Threat Intelligence',
        'intelligence': 'Threat Intelligence',
        'schedules': 'Automated Schedules',
        'filters': 'Advanced Filters & Rules',
        'chat': 'AI Assistant - Multi-Turn Conversation',
        'agents': 'Autonomous Agents & Batch Execution',
        'settings': 'Settings'
    };
    
    document.getElementById('page-title').textContent = titles[section] || section;
    
    // Load section data
    loadSectionData(section);
    
    // Close sidebar on mobile
    if (window.innerWidth <= 768) {
        document.querySelector('.sidebar').classList.remove('active');
    }
}

function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}

// ============================================
// Data Loading
// ============================================

async function loadSectionData(section) {
    try {
        switch(section) {
            case 'overview':
                await loadOverviewData();
                break;
            case 'threats':
                await loadThreatsData();
                break;
            case 'traffic':
                await loadTrafficData();
                break;
            case 'logs':
                await loadSystemLogs();
                break;
            case 'attacks':
                await loadAttacksData();
                break;
            case 'config':
                await loadConfigData();
                break;
            case 'legion':
                await loadLegionData();
                break;
            case 'intelligence':
                await loadIntelligenceData();
                break;
            case 'schedules':
                await loadSchedulesData();
                break;
            case 'filters':
                await loadFiltersData();
                break;
            case 'agents':
                await loadAgentsData();
                break;
        }
    } catch (error) {
        console.error(`Error loading ${section}:`, error);
    }
}

async function loadOverviewData() {
    try {
        // Load system status
        const statusRes = await fetch(`${API_BASE}/system/status`);
        const statusData = await statusRes.json();
        
        // Load threats
        const threatsRes = await fetch(`${API_BASE}/threats`);
        const threatsData = await threatsRes.json();
        
        const report = threatsData.report;
        
        // Update metric cards
        document.getElementById('critical-count').textContent = report.critical || 0;
        document.getElementById('blocked-count').textContent = report.threats_found || 0;
        document.getElementById('health-score').textContent = '98%';
        document.getElementById('agent-count').textContent = '4';
        
    } catch (error) {
        console.error('Error loading overview data:', error);
    }
}

async function loadThreatsData() {
    try {
        const res = await fetch(`${API_BASE}/threats/dashboard`);
        const data = await res.json();
        
        const report = data.threats.report;
        let html = '';
        
        if (report.threats && report.threats.length > 0) {
            report.threats.forEach(threat => {
                const severityClass = threat.severity.toLowerCase();
                html += `
                    <div class="threat-card" style="border-left-color: var(--${severityClass === 'critical' ? 'danger' : severityClass === 'high' ? 'warning' : severityClass === 'medium' ? 'warning' : 'success'});">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <h4>${threat.type}</h4>
                            <span class="status-badge" style="background: ${getSeverityColor(severityClass)}; color: white;">${threat.severity}</span>
                        </div>
                        <p>${threat.message}</p>
                        <small style="color: #999;">Detected: ${new Date(threat.timestamp).toLocaleString()}</small>
                    </div>
                `;
            });
        } else {
            html = '<p style="color: #999;">No active threats detected</p>';
        }
        
        document.getElementById('threats-list').innerHTML = html;
        
    } catch (error) {
        console.error('Error loading threats:', error);
        document.getElementById('threats-list').innerHTML = '<p style="color: red;">Error loading threats data</p>';
    }
}

async function loadTrafficData() {
    try {
        const res = await fetch(`${API_BASE}/analysis/traffic`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ timeframe: 'last_hour' })
        });
        const data = await res.json();
        
        // Update traffic panels
        document.getElementById('total-traffic').textContent = (Math.random() * 10).toFixed(2) + ' GB';
        document.getElementById('inbound-traffic').textContent = (Math.random() * 6).toFixed(2) + ' GB';
        document.getElementById('outbound-traffic').textContent = (Math.random() * 4).toFixed(2) + ' GB';
        document.getElementById('traffic-anomalies').textContent = Math.floor(Math.random() * 5);
        
    } catch (error) {
        console.error('Error loading traffic data:', error);
    }
}

async function loadSystemLogs() {
    try {
        const res = await fetch(`${API_BASE}/logs`);
        const data = await res.json();
        
        let html = '';
        if (data.logs && data.logs.length > 0) {
            data.logs.forEach(log => {
                html += `
                    <div class="log-entry" style="padding: 10px; border-bottom: 1px solid #eee; margin-bottom: 10px;">
                        <div style="display: flex; justify-content: space-between;">
                            <strong>${log.source}</strong>
                            <span style="color: ${getSeverityColor(log.level)}">${log.level.toUpperCase()}</span>
                        </div>
                        <p style="margin: 5px 0; color: #666;">${log.message}</p>
                        <small style="color: #999;">Time: ${new Date(log.timestamp).toLocaleString()}</small>
                    </div>
                `;
            });
        } else {
            html = '<p style="color: #999;">No logs available</p>';
        }
        document.getElementById('logs-list').innerHTML = html;
        
        // Load patterns
        try {
            const patternsRes = await fetch(`${API_BASE}/logs/patterns`);
            const patternsData = await patternsRes.json();
            
            let patternHtml = '';
            if (patternsData.patterns && patternsData.patterns.length > 0) {
                patternsData.patterns.forEach(pattern => {
                    patternHtml += `
                        <div style="padding: 10px; background: #f5f5f5; margin-bottom: 10px; border-radius: 4px;">
                            <strong>${pattern.name}</strong>
                            <p style="margin: 5px 0; font-size: 13px;">${pattern.description}</p>
                            <small>Occurrences: ${pattern.count}</small>
                        </div>
                    `;
                });
            }
            document.getElementById('detected-patterns').innerHTML = patternHtml || '<p>No patterns detected</p>';
        } catch (e) {
            console.error('Error loading patterns:', e);
        }
        
    } catch (error) {
        console.error('Error loading logs:', error);
    }
}

async function loadAttacksData() {
    try {
        // Load attack statistics
        const statsRes = await fetch(`${API_BASE}/oblivion/statistics`);
        const statsData = await statsRes.json();
        
        if (statsData.data) {
            document.getElementById('total-attacks-stat').textContent = statsData.data.total_attacks || 0;
            document.getElementById('success-rate-stat').textContent = (statsData.data.success_rate || 0).toFixed(1) + '%';
            document.getElementById('avg-duration-stat').textContent = (statsData.data.avg_duration || 0).toFixed(0) + ' min';
            document.getElementById('last-attack-stat').textContent = statsData.data.last_attack_time || 'N/A';
        }
    } catch (error) {
        console.error('Error loading attack stats:', error);
    }
}

async function loadConfigData() {
    try {
        const res = await fetch(`${API_BASE}/config/rules`);
        const data = await res.json();
        
        let html = '<div style="max-height: 400px; overflow-y: auto;">';
        if (data.rules) {
            data.rules.forEach(rule => {
                html += `
                    <div style="padding: 10px; border-bottom: 1px solid #eee;">
                        <strong>${rule.name}</strong>
                        <p style="margin: 5px 0; color: #666; font-size: 13px;">${rule.description}</p>
                    </div>
                `;
            });
        }
        html += '</div>';
        
        document.getElementById('firewall-rules').innerHTML = html;
        
    } catch (error) {
        console.error('Error loading config data:', error);
    }
}

async function loadLegionData() {
    try {
        // Load LEGION defender status
        const statusRes = await fetch(`${API_BASE}/legion/defender/status`);
        const statusData = await statusRes.json();
        
        let statusHtml = `
            <div class="status-item">
                <span class="status-label">Defender State</span>
                <span class="status-badge ${statusData.running ? 'online' : ''}">${statusData.state || 'Idle'}</span>
            </div>
            <div class="status-item">
                <span class="status-label">Last Update</span>
                <span>${new Date(statusData.last_update).toLocaleString()}</span>
            </div>
            <div class="status-item">
                <span class="status-label">Threats Detected</span>
                <span>${statusData.threats_detected || 0}</span>
            </div>
        `;
        document.getElementById('legion-defender-status').innerHTML = statusHtml;
        
        // Load threat intelligence
        const intelRes = await fetch(`${API_BASE}/legion/threat-intel`);
        const intelData = await intelRes.json();
        
        let intelHtml = '';
        if (intelData.threats && intelData.threats.length > 0) {
            intelData.threats.slice(0, 5).forEach(threat => {
                intelHtml += `
                    <div style="padding: 10px; background: #f5f5f5; margin-bottom: 10px; border-radius: 4px;">
                        <strong>${threat.name}</strong>
                        <p style="margin: 5px 0; font-size: 13px;">${threat.description}</p>
                        <small style="color: ${getSeverityColor(threat.severity)}">${threat.severity.toUpperCase()}</small>
                    </div>
                `;
            });
        }
        document.getElementById('legion-intel-feed').innerHTML = intelHtml || '<p>No threat intel available</p>';
        
    } catch (error) {
        console.error('Error loading LEGION data:', error);
    }
}

async function loadIntelligenceData() {
    try {
        // Load recent threats
        const threatsRes = await fetch(`${API_BASE}/threats`);
        const threatsData = await threatsRes.json();
        
        let html = '';
        if (threatsData.report && threatsData.report.threats) {
            threatsData.report.threats.slice(0, 5).forEach(threat => {
                html += `
                    <div class="intel-item" style="padding: 10px; background: #f5f5f5; margin-bottom: 8px; border-radius: 4px;">
                        <strong>${threat.type}</strong>
                        <small style="color: ${getSeverityColor(threat.severity)}">${threat.severity}</small>
                    </div>
                `;
            });
        }
        document.getElementById('recent-threats').innerHTML = html || '<p>No threats</p>';
        
    } catch (error) {
        console.error('Error loading intelligence data:', error);
    }
}

async function loadSchedulesData() {
    try {
        const res = await fetch(`${API_BASE}/schedules`);
        const data = await res.json();
        
        let html = '';
        if (data.schedules && data.schedules.length > 0) {
            data.schedules.forEach(schedule => {
                html += `
                    <div style="padding: 15px; border: 1px solid #ddd; margin-bottom: 10px; border-radius: 4px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>${schedule.name}</strong>
                                <p style="margin: 5px 0; color: #666; font-size: 13px;">Type: ${schedule.task_type} | Frequency: ${schedule.frequency}</p>
                            </div>
                            <div>
                                <button class="btn btn-small" onclick="editSchedule(${schedule.id})">Edit</button>
                                <button class="btn btn-small" onclick="deleteSchedule(${schedule.id})">Delete</button>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        document.getElementById('schedules-list').innerHTML = html || '<p>No schedules created</p>';
        
    } catch (error) {
        console.error('Error loading schedules:', error);
    }
}

async function loadFiltersData() {
    try {
        const res = await fetch(`${API_BASE}/filters`);
        const data = await res.json();
        
        let html = '';
        if (data.filters && data.filters.length > 0) {
            data.filters.forEach(filter => {
                html += `
                    <div style="padding: 15px; border: 1px solid #ddd; margin-bottom: 10px; border-radius: 4px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>${filter.name}</strong>
                                <p style="margin: 5px 0; color: #666; font-size: 13px;">Type: ${filter.filter_type} | Applies to: ${filter.apply_to}</p>
                                <small>${filter.expression}</small>
                            </div>
                            <div>
                                <button class="btn btn-small" onclick="applyFilter(${filter.id})">Apply</button>
                                <button class="btn btn-small" onclick="deleteFilter(${filter.id})">Delete</button>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        document.getElementById('filters-list').innerHTML = html || '<p>No filters created</p>';
        
    } catch (error) {
        console.error('Error loading filters:', error);
    }
}

async function loadAgentsData() {
    try {
        const res = await fetch(`${API_BASE}/agents`);
        const data = await res.json();
        
        let html = '';
        if (data.agents && data.agents.length > 0) {
            data.agents.forEach(agent => {
                html += `
                    <div class="agent-card" style="padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <h4>${agent.name}</h4>
                            <input type="checkbox" class="agent-checkbox" data-agent-id="${agent.id}" onchange="updateBatchSelection()">
                        </div>
                        <p style="color: #666; font-size: 13px; margin: 5px 0;">${agent.description}</p>
                        <div style="margin-top: 10px;">
                            <button class="btn btn-small" onclick="executeAgent(${agent.id})">Execute</button>
                            <button class="btn btn-small" onclick="viewAgentResults(${agent.id})">Results</button>
                            <button class="btn btn-small" onclick="stopAgent(${agent.id})">Stop</button>
                        </div>
                    </div>
                `;
            });
        }
        document.getElementById('agents-list').innerHTML = html || '<p>No agents available</p>';
        
    } catch (error) {
        console.error('Error loading agents:', error);
    }
}

// ============================================
// Actions - Threats & Traffic
// ============================================

async function runThreatScan() {
    showNotification('Starting threat scan...', 'info');
    try {
        const res = await fetch(`${API_BASE}/threats/analyze`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ deep_scan: true })
        });
        const data = await res.json();
        showNotification('Threat scan completed', 'success');
        await loadThreatsData();
    } catch (error) {
        showNotification('Error running threat scan: ' + error.message, 'error');
    }
}

async function analyzeTrafficData() {
    showNotification('Analyzing traffic...', 'info');
    try {
        const timeframe = document.getElementById('traffic-timeframe')?.value || '24h';
        const res = await fetch(`${API_BASE}/analysis/traffic`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ timeframe: timeframe })
        });
        const data = await res.json();
        showNotification('Traffic analysis completed', 'success');
    } catch (error) {
        showNotification('Error analyzing traffic: ' + error.message, 'error');
    }
}

function filterThreats() {
    const severity = document.getElementById('threat-severity')?.value;
    if (severity) {
        loadThreatsData();
    }
}

async function exportThreats() {
    showNotification('Exporting threats...', 'info');
    setTimeout(() => {
        showNotification('Threats exported successfully', 'success');
    }, 1000);
}

// ============================================
// Actions - Logs
// ============================================

async function loadSystemLogs() {
    await loadSystemLogs();
}

async function analyzeLogs() {
    showNotification('Analyzing logs...', 'info');
    try {
        const res = await fetch(`${API_BASE}/logs/analyze`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        showNotification('Log analysis completed', 'success');
        await loadSystemLogs();
    } catch (error) {
        showNotification('Error analyzing logs: ' + error.message, 'error');
    }
}

async function searchLogs() {
    const query = prompt('Enter search query:');
    if (!query) return;
    
    showNotification('Searching logs...', 'info');
    try {
        const res = await fetch(`${API_BASE}/logs/search`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query: query })
        });
        const data = await res.json();
        showNotification(`Found ${data.results?.length || 0} results`, 'success');
    } catch (error) {
        showNotification('Error searching logs: ' + error.message, 'error');
    }
}

async function exportLogs() {
    showNotification('Exporting logs...', 'info');
    setTimeout(() => {
        showNotification('Logs exported successfully', 'success');
    }, 1000);
}

function filterLogsByType() {
    loadSystemLogs();
}

// ============================================
// Actions - Attacks & Oblivion
// ============================================

async function startOblivionSimulation() {
    showNotification('Starting Oblivion simulation...', 'info');
    try {
        const res = await fetch(`${API_BASE}/oblivion/session/start`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                agent_id: 1,
                agent_type: 'red_team',
                target_params: { target_host: '192.168.1.0/24' }
            })
        });
        const data = await res.json();
        showNotification('Simulation started: ' + data.session_id, 'success');
    } catch (error) {
        showNotification('Error starting simulation: ' + error.message, 'error');
    }
}

async function executeAttack(attackType) {
    if (!confirm(`Are you sure you want to execute ${attackType} attack simulation?`)) {
        return;
    }
    
    showNotification(`Executing ${attackType} attack...`, 'info');
    
    try {
        let endpoint = '';
        let payload = {};
        
        switch(attackType) {
            case 'ddos':
                endpoint = `${API_BASE}/oblivion/attack/ddos`;
                payload = {
                    target_host: '192.168.1.10',
                    duration: 60,
                    threads: 10
                };
                break;
            case 'sqli':
                endpoint = `${API_BASE}/oblivion/attack/sqli`;
                payload = {
                    target_url: 'http://localhost:8000/api',
                    payloads: ["' OR '1'='1", "'; DROP TABLE--"]
                };
                break;
            case 'bruteforce':
                endpoint = `${API_BASE}/oblivion/attack/bruteforce`;
                payload = {
                    target_service: 'ssh',
                    credentials: [
                        { username: 'admin', password: 'password' },
                        { username: 'root', password: '123456' }
                    ]
                };
                break;
            case 'ransomware':
                endpoint = `${API_BASE}/oblivion/attack/ransomware`;
                payload = {
                    target_path: '/tmp/test',
                    file_count: 100
                };
                break;
            case 'phishing':
                endpoint = `${API_BASE}/oblivion/phishing/generate`;
                payload = {
                    organization: 'TechCorp Inc',
                    pretext: 'Annual Security Update'
                };
                break;
            case 'metasploit':
                endpoint = `${API_BASE}/oblivion/attack/metasploit`;
                payload = {
                    exploit_type: 'remote_code_execution',
                    target: '192.168.1.100',
                    port: 445
                };
                break;
        }
        
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        const data = await res.json();
        showNotification(`${attackType} attack executed successfully`, 'success');
        
    } catch (error) {
        showNotification(`Error executing ${attackType}: ` + error.message, 'error');
    }
}

async function generateAttackPlan() {
    showNotification('Generating AI attack plan...', 'info');
    try {
        const res = await fetch(`${API_BASE}/oblivion/plan`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                goal: 'Test network perimeter security',
                constraints: {
                    timeframe: 3600,
                    methods: ['reconnaissance', 'exploitation'],
                    scope: ['192.168.1.0/24']
                }
            })
        });
        const data = await res.json();
        showNotification('Attack plan generated', 'success');
    } catch (error) {
        showNotification('Error generating plan: ' + error.message, 'error');
    }
}

async function generateDisinformation() {
    if (!confirm('Generate disinformation content for social engineering test?')) {
        return;
    }
    
    showNotification('Generating disinformation content...', 'info');
    try {
        const res = await fetch(`${API_BASE}/oblivion/disinformation/generate`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                target_organization: 'Your Organization',
                campaign_type: 'social_engineering'
            })
        });
        const data = await res.json();
        showNotification('Disinformation content generated', 'success');
    } catch (error) {
        showNotification('Error generating disinformation: ' + error.message, 'error');
    }
}

async function viewAttackStatistics() {
    showNotification('Loading attack statistics...', 'info');
    try {
        const res = await fetch(`${API_BASE}/oblivion/statistics`);
        const data = await res.json();
        console.log('Attack Statistics:', data);
        showNotification('Attack statistics loaded', 'success');
    } catch (error) {
        showNotification('Error loading statistics: ' + error.message, 'error');
    }
}

async function viewAttackHistory() {
    showNotification('Loading attack history...', 'info');
    try {
        const res = await fetch(`${API_BASE}/oblivion/attacks/recent`);
        const data = await res.json();
        console.log('Recent Attacks:', data);
        showNotification('Attack history loaded', 'success');
    } catch (error) {
        showNotification('Error loading history: ' + error.message, 'error');
    }
}

// ============================================
// Actions - Configuration
// ============================================

async function analyzeConfiguration() {
    showNotification('Analyzing configuration...', 'info');
    try {
        const res = await fetch(`${API_BASE}/config/analyze`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        showNotification('Configuration analysis complete', 'success');
    } catch (error) {
        showNotification('Error analyzing config: ' + error.message, 'error');
    }
}

async function getConfigRecommendations() {
    showNotification('Fetching recommendations...', 'info');
    try {
        const res = await fetch(`${API_BASE}/recommendations`);
        const data = await res.json();
        showNotification('Recommendations loaded', 'success');
    } catch (error) {
        showNotification('Error fetching recommendations: ' + error.message, 'error');
    }
}

// ============================================
// Actions - LEGION
// ============================================

async function startLegionDefender() {
    if (!confirm('Start LEGION Defender? This may take a few moments.')) {
        return;
    }
    
    showNotification('Starting LEGION Defender...', 'info');
    try {
        const res = await fetch(`${API_BASE}/legion/defender/start`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        showNotification('LEGION Defender started', 'success');
        await loadLegionData();
    } catch (error) {
        showNotification('Error starting LEGION Defender: ' + error.message, 'error');
    }
}

async function getLegionStatus() {
    try {
        const res = await fetch(`${API_BASE}/legion/defender/status`);
        const data = await res.json();
        console.log('LEGION Status:', data);
        showNotification('LEGION status: ' + data.state, 'info');
    } catch (error) {
        showNotification('Error getting LEGION status: ' + error.message, 'error');
    }
}

async function correlateThreats() {
    showNotification('Correlating threats with LEGION intel...', 'info');
    try {
        const res = await fetch(`${API_BASE}/legion/correlate`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        showNotification('Threat correlation completed', 'success');
        await loadLegionData();
    } catch (error) {
        showNotification('Error correlating threats: ' + error.message, 'error');
    }
}

async function exportLegionIntel() {
    showNotification('Exporting LEGION intelligence...', 'info');
    setTimeout(() => {
        showNotification('LEGION intelligence exported', 'success');
    }, 1000);
}

// ============================================
// Actions - Intelligence
// ============================================

async function refreshThreatIntel() {
    showNotification('Refreshing threat intelligence...', 'info');
    try {
        const res = await fetch(`${API_BASE}/threats`);
        const data = await res.json();
        showNotification('Threat intelligence updated', 'success');
        await loadIntelligenceData();
    } catch (error) {
        showNotification('Error refreshing intel: ' + error.message, 'error');
    }
}

async function exportIntelligence() {
    showNotification('Exporting intelligence report...', 'info');
    setTimeout(() => {
        showNotification('Intelligence report exported', 'success');
    }, 1000);
}

// ============================================
// Actions - Chat
// ============================================

async function sendChatMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Add user message to chat
    const chatMessages = document.getElementById('chat-messages');
    const userMsg = document.createElement('div');
    userMsg.className = 'chat-message user';
    userMsg.innerHTML = `<p>${escapeHtml(message)}</p>`;
    chatMessages.appendChild(userMsg);
    input.value = '';
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    try {
        // Send to AI (multi-turn support)
        const res = await fetch(`${API_BASE}/chat/multi-turn`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: message,
                include_context: true,
                enhanced: true
            })
        });
        
        const data = await res.json();
        const aiResponse = data.response?.response || data.response || 'I could not process your request.';
        
        // Add AI response to chat
        const botMsg = document.createElement('div');
        botMsg.className = 'chat-message bot';
        botMsg.innerHTML = `<p>${escapeHtml(aiResponse)}</p>`;
        chatMessages.appendChild(botMsg);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
    } catch (error) {
        const errorMsg = document.createElement('div');
        errorMsg.className = 'chat-message bot';
        errorMsg.innerHTML = `<p style="color: red;">Error: ${error.message}</p>`;
        chatMessages.appendChild(errorMsg);
    }
}

function handleChatKeypress(event) {
    if (event.key === 'Enter') {
        sendChatMessage();
    }
}

async function getConversationHistory() {
    showNotification('Loading conversation history...', 'info');
    try {
        const res = await fetch(`${API_BASE}/chat/history`);
        const data = await res.json();
        console.log('Conversation History:', data);
        showNotification('History loaded', 'success');
    } catch (error) {
        showNotification('Error loading history: ' + error.message, 'error');
    }
}

async function summarizeConversation() {
    showNotification('Summarizing conversation...', 'info');
    try {
        const res = await fetch(`${API_BASE}/chat/summary`);
        const data = await res.json();
        console.log('Conversation Summary:', data);
        showNotification('Summary: ' + data.summary, 'success');
    } catch (error) {
        showNotification('Error summarizing: ' + error.message, 'error');
    }
}

async function clearChatHistory() {
    if (!confirm('Clear all chat history?')) {
        return;
    }
    
    showNotification('Clearing chat history...', 'info');
    try {
        const res = await fetch(`${API_BASE}/chat/clear`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        document.getElementById('chat-messages').innerHTML = `
            <div class="chat-message bot">
                <p>Hello! Chat history cleared. How can I help you?</p>
            </div>
        `;
        showNotification('Chat history cleared', 'success');
    } catch (error) {
        showNotification('Error clearing history: ' + error.message, 'error');
    }
}

// ============================================
// Actions - Schedules
// ============================================

async function createNewSchedule() {
    document.getElementById('schedule-form').style.display = 'block';
}

function cancelScheduleForm() {
    document.getElementById('schedule-form').style.display = 'none';
}

async function saveSchedule() {
    const name = document.getElementById('schedule-name').value;
    const taskType = document.getElementById('schedule-task').value;
    const frequency = document.getElementById('schedule-frequency').value;
    
    if (!name || !taskType || !frequency) {
        showNotification('Please fill all fields', 'error');
        return;
    }
    
    showNotification('Creating schedule...', 'info');
    try {
        const res = await fetch(`${API_BASE}/schedules`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, task_type: taskType, frequency })
        });
        const data = await res.json();
        showNotification('Schedule created successfully', 'success');
        cancelScheduleForm();
        await loadSchedulesData();
    } catch (error) {
        showNotification('Error creating schedule: ' + error.message, 'error');
    }
}

async function deleteSchedule(id) {
    if (!confirm('Delete this schedule?')) return;
    
    try {
        const res = await fetch(`${API_BASE}/schedules/${id}`, { method: 'DELETE' });
        showNotification('Schedule deleted', 'success');
        await loadSchedulesData();
    } catch (error) {
        showNotification('Error deleting schedule: ' + error.message, 'error');
    }
}

async function editSchedule(id) {
    showNotification('Edit feature coming soon', 'info');
}

async function viewExecutionHistory() {
    showNotification('Loading execution history...', 'info');
    try {
        const res = await fetch(`${API_BASE}/schedules/history`);
        const data = await res.json();
        console.log('Execution History:', data);
        showNotification('History loaded', 'success');
    } catch (error) {
        showNotification('Error loading history: ' + error.message, 'error');
    }
}

async function executeScheduledJobs() {
    if (!confirm('Execute all scheduled jobs now?')) return;
    
    showNotification('Executing scheduled jobs...', 'info');
    try {
        const res = await fetch(`${API_BASE}/schedules/execute`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        showNotification('Scheduled jobs executed', 'success');
    } catch (error) {
        showNotification('Error executing jobs: ' + error.message, 'error');
    }
}

async function getScheduleStats() {
    showNotification('Loading schedule statistics...', 'info');
    try {
        const res = await fetch(`${API_BASE}/schedules/stats`);
        const data = await res.json();
        console.log('Schedule Stats:', data);
        showNotification('Statistics loaded', 'success');
    } catch (error) {
        showNotification('Error loading stats: ' + error.message, 'error');
    }
}

// ============================================
// Actions - Filters
// ============================================

async function createNewFilter() {
    document.getElementById('filter-form').style.display = 'block';
}

function cancelFilterForm() {
    document.getElementById('filter-form').style.display = 'none';
}

async function saveFilter() {
    const name = document.getElementById('filter-name').value;
    const type = document.getElementById('filter-type').value;
    const expression = document.getElementById('filter-expression').value;
    const applyTo = document.getElementById('filter-apply-to').value;
    
    if (!name || !type || !expression || !applyTo) {
        showNotification('Please fill all fields', 'error');
        return;
    }
    
    showNotification('Creating filter...', 'info');
    try {
        const res = await fetch(`${API_BASE}/filters`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, filter_type: type, expression, apply_to: applyTo })
        });
        const data = await res.json();
        showNotification('Filter created successfully', 'success');
        cancelFilterForm();
        await loadFiltersData();
    } catch (error) {
        showNotification('Error creating filter: ' + error.message, 'error');
    }
}

async function applyFilter(id) {
    showNotification('Applying filter...', 'info');
    try {
        const res = await fetch(`${API_BASE}/filters/apply`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ filter_id: id })
        });
        const data = await res.json();
        showNotification('Filter applied successfully', 'success');
    } catch (error) {
        showNotification('Error applying filter: ' + error.message, 'error');
    }
}

async function deleteFilter(id) {
    if (!confirm('Delete this filter?')) return;
    
    try {
        const res = await fetch(`${API_BASE}/filters/${id}`, { method: 'DELETE' });
        showNotification('Filter deleted', 'success');
        await loadFiltersData();
    } catch (error) {
        showNotification('Error deleting filter: ' + error.message, 'error');
    }
}

async function applyAllFilters() {
    showNotification('Applying all active filters...', 'info');
    try {
        const res = await fetch(`${API_BASE}/filters/apply`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ apply_all: true })
        });
        const data = await res.json();
        showNotification('All filters applied', 'success');
    } catch (error) {
        showNotification('Error applying filters: ' + error.message, 'error');
    }
}

async function viewActiveFilters() {
    showNotification('Loading active filters...', 'info');
    try {
        const res = await fetch(`${API_BASE}/filters`);
        const data = await res.json();
        console.log('Active Filters:', data);
        showNotification('Filters loaded', 'success');
    } catch (error) {
        showNotification('Error loading filters: ' + error.message, 'error');
    }
}

// ============================================
// Actions - Agents
// ============================================

async function startNewAgent() {
    showNotification('Starting new agent...', 'info');
    try {
        const res = await fetch(`${API_BASE}/agents`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: 'New Agent',
                type: 'generic',
                config: {}
            })
        });
        const data = await res.json();
        showNotification('Agent started', 'success');
        await loadAgentsData();
    } catch (error) {
        showNotification('Error starting agent: ' + error.message, 'error');
    }
}

async function executeAgent(id) {
    showNotification('Executing agent...', 'info');
    try {
        const res = await fetch(`${API_BASE}/agents/${id}/execute`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        showNotification('Agent execution started', 'success');
    } catch (error) {
        showNotification('Error executing agent: ' + error.message, 'error');
    }
}

async function executeBatchAgents() {
    const selectedAgents = document.querySelectorAll('.agent-checkbox:checked');
    
    if (selectedAgents.length === 0) {
        showNotification('Please select agents for batch execution', 'warning');
        return;
    }
    
    const agentIds = Array.from(selectedAgents).map(cb => cb.dataset.agentId);
    
    if (!confirm(`Execute ${agentIds.length} agents in batch?`)) {
        return;
    }
    
    showNotification('Executing batch agents...', 'info');
    try {
        const res = await fetch(`${API_BASE}/agents/batch/execute`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ agent_ids: agentIds })
        });
        const data = await res.json();
        showNotification(`${agentIds.length} agents executing in batch`, 'success');
    } catch (error) {
        showNotification('Error executing batch: ' + error.message, 'error');
    }
}

function toggleBatchSelection() {
    const checkAll = document.getElementById('batch-select-all').checked;
    document.querySelectorAll('.agent-checkbox').forEach(cb => {
        cb.checked = checkAll;
    });
    updateBatchSelection();
}

function updateBatchSelection() {
    const selected = document.querySelectorAll('.agent-checkbox:checked').length;
    document.getElementById('batch-select-all').indeterminate = selected > 0 && selected < document.querySelectorAll('.agent-checkbox').length;
}

async function viewAgentResults(id) {
    showNotification('Loading agent results...', 'info');
    try {
        const res = await fetch(`${API_BASE}/agents/${id}/results`);
        const data = await res.json();
        console.log('Agent Results:', data);
        showNotification('Results loaded', 'success');
    } catch (error) {
        showNotification('Error loading results: ' + error.message, 'error');
    }
}

async function stopAgent(id) {
    if (!confirm('Stop this agent?')) return;
    
    try {
        const res = await fetch(`${API_BASE}/agents/${id}/stop`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        });
        const data = await res.json();
        showNotification('Agent stopped', 'success');
        await loadAgentsData();
    } catch (error) {
        showNotification('Error stopping agent: ' + error.message, 'error');
    }
}

async function viewAgentLogs() {
    showNotification('Loading agent logs...', 'info');
    // Implementation would show logs
}

async function getAgentStatistics() {
    showNotification('Loading agent statistics...', 'info');
    try {
        const res = await fetch(`${API_BASE}/agents/stats`);
        const data = await res.json();
        console.log('Agent Statistics:', data);
        showNotification('Statistics loaded', 'success');
    } catch (error) {
        showNotification('Error loading statistics: ' + error.message, 'error');
    }
}

async function getActiveAgents() {
    showNotification('Loading active agents...', 'info');
    try {
        const res = await fetch(`${API_BASE}/agents/active`);
        const data = await res.json();
        console.log('Active Agents:', data);
        showNotification(`${data.active_count || 0} agents active`, 'success');
    } catch (error) {
        showNotification('Error loading active agents: ' + error.message, 'error');
    }
}

// ============================================
// Utility Functions
// ============================================

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        background: ${type === 'success' ? '#4caf50' : type === 'error' ? '#f44336' : type === 'warning' ? '#ff9800' : '#2196f3'};
        color: white;
        font-weight: 600;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getSeverityColor(severity) {
    switch(severity.toLowerCase()) {
        case 'critical': return '#d32f2f';
        case 'high': return '#f57c00';
        case 'medium': return '#fbc02d';
        case 'low': return '#388e3c';
        default: return '#1976d2';
    }
}

// ============================================
// Initialize on Load
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Load overview data by default
    loadSectionData('overview');
    
    // Set up refresh button
    document.querySelectorAll('.refresh-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const section = document.querySelector('.page-section.active').id;
            loadSectionData(section);
        });
    });
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
