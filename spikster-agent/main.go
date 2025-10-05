package main

import (
	"encoding/json"
	"log"
	"net/http"
	"runtime"
	"time"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"
	"github.com/go-chi/cors"
	"github.com/shirou/gopsutil/v3/cpu"
	"github.com/shirou/gopsutil/v3/disk"
	"github.com/shirou/gopsutil/v3/host"
	"github.com/shirou/gopsutil/v3/load"
	"github.com/shirou/gopsutil/v3/mem"
	"github.com/shirou/gopsutil/v3/net"
)

const (
	Version = "1.0.0"
	Port    = ":9273"
)

// Metrics represents all system metrics
type Metrics struct {
	Timestamp time.Time       `json:"timestamp"`
	CPU       float64         `json:"cpu_percent"`
	Memory    MemoryMetrics   `json:"memory"`
	Disk      DiskMetrics     `json:"disk"`
	Load      LoadMetrics     `json:"load"`
	Network   NetworkMetrics  `json:"network"`
	Uptime    uint64          `json:"uptime_seconds"`
	CPUCores  int             `json:"cpu_cores"`
}

// MemoryMetrics represents memory statistics
type MemoryMetrics struct {
	Total       uint64  `json:"total_bytes"`
	Used        uint64  `json:"used_bytes"`
	Free        uint64  `json:"free_bytes"`
	Available   uint64  `json:"available_bytes"`
	Percent     float64 `json:"percent"`
	Cached      uint64  `json:"cached_bytes"`
	Buffers     uint64  `json:"buffers_bytes"`
}

// DiskMetrics represents disk statistics
type DiskMetrics struct {
	Total       uint64  `json:"total_bytes"`
	Used        uint64  `json:"used_bytes"`
	Free        uint64  `json:"free_bytes"`
	Percent     float64 `json:"percent"`
}

// LoadMetrics represents system load
type LoadMetrics struct {
	Load1       float64 `json:"load1"`
	Load5       float64 `json:"load5"`
	Load15      float64 `json:"load15"`
}

// NetworkMetrics represents network I/O
type NetworkMetrics struct {
	BytesSent   uint64 `json:"bytes_sent"`
	BytesRecv   uint64 `json:"bytes_recv"`
	PacketsSent uint64 `json:"packets_sent"`
	PacketsRecv uint64 `json:"packets_recv"`
}

// HealthResponse represents health check response
type HealthResponse struct {
	Status  string `json:"status"`
	Version string `json:"version"`
	Uptime  uint64 `json:"uptime_seconds"`
}

// getMetrics collects all system metrics
func getMetrics() (*Metrics, error) {
	m := &Metrics{
		Timestamp: time.Now(),
		CPUCores:  runtime.NumCPU(),
	}

	// CPU usage
	cpuPercent, err := cpu.Percent(time.Second, false)
	if err == nil && len(cpuPercent) > 0 {
		m.CPU = cpuPercent[0]
	}

	// Memory
	vmStat, err := mem.VirtualMemory()
	if err == nil {
		m.Memory = MemoryMetrics{
			Total:     vmStat.Total,
			Used:      vmStat.Used,
			Free:      vmStat.Free,
			Available: vmStat.Available,
			Percent:   vmStat.UsedPercent,
			Cached:    vmStat.Cached,
			Buffers:   vmStat.Buffers,
		}
	}

	// Disk (root partition)
	diskStat, err := disk.Usage("/")
	if err == nil {
		m.Disk = DiskMetrics{
			Total:   diskStat.Total,
			Used:    diskStat.Used,
			Free:    diskStat.Free,
			Percent: diskStat.UsedPercent,
		}
	}

	// Load average
	loadStat, err := load.Avg()
	if err == nil {
		m.Load = LoadMetrics{
			Load1:  loadStat.Load1,
			Load5:  loadStat.Load5,
			Load15: loadStat.Load15,
		}
	}

	// Network I/O
	netIO, err := net.IOCounters(false)
	if err == nil && len(netIO) > 0 {
		m.Network = NetworkMetrics{
			BytesSent:   netIO[0].BytesSent,
			BytesRecv:   netIO[0].BytesRecv,
			PacketsSent: netIO[0].PacketsSent,
			PacketsRecv: netIO[0].PacketsRecv,
		}
	}

	// Uptime
	uptime, err := host.Uptime()
	if err == nil {
		m.Uptime = uptime
	}

	return m, nil
}

// metricsHandler handles /metrics endpoint
func metricsHandler(w http.ResponseWriter, r *http.Request) {
	metrics, err := getMetrics()
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	if err := json.NewEncoder(w).Encode(metrics); err != nil {
		log.Printf("Error encoding metrics: %v", err)
	}
}

// healthHandler handles /health endpoint
func healthHandler(w http.ResponseWriter, r *http.Request) {
	uptime, _ := host.Uptime()
	
	health := HealthResponse{
		Status:  "healthy",
		Version: Version,
		Uptime:  uptime,
	}

	w.Header().Set("Content-Type", "application/json")
	if err := json.NewEncoder(w).Encode(health); err != nil {
		log.Printf("Error encoding health response: %v", err)
	}
}

// rootHandler handles / endpoint
func rootHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]string{
		"service": "Spikster Agent",
		"version": Version,
		"endpoints": "/metrics, /health",
	})
}

func main() {
	r := chi.NewRouter()

	// Middleware
	r.Use(middleware.Logger)
	r.Use(middleware.Recoverer)
	r.Use(middleware.RequestID)
	r.Use(middleware.RealIP)
	r.Use(middleware.Timeout(30 * time.Second))

	// CORS
	r.Use(cors.Handler(cors.Options{
		AllowedOrigins:   []string{"*"},
		AllowedMethods:   []string{"GET", "OPTIONS"},
		AllowedHeaders:   []string{"Accept", "Content-Type"},
		ExposedHeaders:   []string{"Link"},
		AllowCredentials: false,
		MaxAge:           300,
	}))

	// Routes
	r.Get("/", rootHandler)
	r.Get("/health", healthHandler)
	r.Get("/metrics", metricsHandler)

	// Start server
	log.Printf("🚀 Spikster Agent v%s starting on port %s", Version, Port)
	log.Printf("📊 Endpoints: /health, /metrics")
	log.Printf("💻 CPU Cores: %d", runtime.NumCPU())
	
	if err := http.ListenAndServe(Port, r); err != nil {
		log.Fatalf("Failed to start server: %v", err)
	}
}
