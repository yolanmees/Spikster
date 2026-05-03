package metrics

import (
	"encoding/json"
	"log"
	"net/http"
	"runtime"
	"time"

	"github.com/shirou/gopsutil/v3/cpu"
	"github.com/shirou/gopsutil/v3/disk"
	"github.com/shirou/gopsutil/v3/host"
	"github.com/shirou/gopsutil/v3/load"
	"github.com/shirou/gopsutil/v3/mem"
	"github.com/shirou/gopsutil/v3/net"
)

const Version = "1.0.0"

type Metrics struct {
	Timestamp time.Time      `json:"timestamp"`
	CPU       float64        `json:"cpu_percent"`
	Memory    MemoryMetrics  `json:"memory"`
	Disk      DiskMetrics    `json:"disk"`
	Load      LoadMetrics    `json:"load"`
	Network   NetworkMetrics `json:"network"`
	Uptime    uint64         `json:"uptime_seconds"`
	CPUCores  int            `json:"cpu_cores"`
}

type MemoryMetrics struct {
	Total     uint64  `json:"total_bytes"`
	Used      uint64  `json:"used_bytes"`
	Free      uint64  `json:"free_bytes"`
	Available uint64  `json:"available_bytes"`
	Percent   float64 `json:"percent"`
	Cached    uint64  `json:"cached_bytes"`
	Buffers   uint64  `json:"buffers_bytes"`
}

type DiskMetrics struct {
	Total   uint64  `json:"total_bytes"`
	Used    uint64  `json:"used_bytes"`
	Free    uint64  `json:"free_bytes"`
	Percent float64 `json:"percent"`
}

type LoadMetrics struct {
	Load1  float64 `json:"load1"`
	Load5  float64 `json:"load5"`
	Load15 float64 `json:"load15"`
}

type NetworkMetrics struct {
	BytesSent   uint64 `json:"bytes_sent"`
	BytesRecv   uint64 `json:"bytes_recv"`
	PacketsSent uint64 `json:"packets_sent"`
	PacketsRecv uint64 `json:"packets_recv"`
}

type HealthResponse struct {
	Status  string `json:"status"`
	Version string `json:"version"`
	Uptime  uint64 `json:"uptime_seconds"`
}

// Collect gathers all system metrics.
func Collect() (*Metrics, error) {
	m := &Metrics{
		Timestamp: time.Now(),
		CPUCores:  runtime.NumCPU(),
	}

	cpuPercent, err := cpu.Percent(time.Second, false)
	if err == nil && len(cpuPercent) > 0 {
		m.CPU = cpuPercent[0]
	}

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

	diskStat, err := disk.Usage("/")
	if err == nil {
		m.Disk = DiskMetrics{
			Total:   diskStat.Total,
			Used:    diskStat.Used,
			Free:    diskStat.Free,
			Percent: diskStat.UsedPercent,
		}
	}

	loadStat, err := load.Avg()
	if err == nil {
		m.Load = LoadMetrics{
			Load1:  loadStat.Load1,
			Load5:  loadStat.Load5,
			Load15: loadStat.Load15,
		}
	}

	netIO, err := net.IOCounters(false)
	if err == nil && len(netIO) > 0 {
		m.Network = NetworkMetrics{
			BytesSent:   netIO[0].BytesSent,
			BytesRecv:   netIO[0].BytesRecv,
			PacketsSent: netIO[0].PacketsSent,
			PacketsRecv: netIO[0].PacketsRecv,
		}
	}

	uptime, err := host.Uptime()
	if err == nil {
		m.Uptime = uptime
	}

	return m, nil
}

// CollectJSON returns metrics as a JSON string.
func CollectJSON() (string, error) {
	m, err := Collect()
	if err != nil {
		return "", err
	}
	b, err := json.Marshal(m)
	return string(b), err
}

// StartHTTPServer starts the metrics HTTP server on the given port (blocking).
func StartHTTPServer(port string) {
	mux := http.NewServeMux()

	mux.HandleFunc("/health", func(w http.ResponseWriter, r *http.Request) {
		uptime, _ := host.Uptime()
		resp := HealthResponse{
			Status:  "healthy",
			Version: Version,
			Uptime:  uptime,
		}
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(resp)
	})

	mux.HandleFunc("/metrics", func(w http.ResponseWriter, r *http.Request) {
		m, err := Collect()
		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(m)
	})

	mux.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]string{
			"service":   "Spikster Daemon",
			"version":   Version,
			"endpoints": "/health, /metrics",
		})
	})

	log.Printf("metrics HTTP server on %s", port)
	if err := http.ListenAndServe(port, mux); err != nil {
		log.Printf("metrics HTTP server: %v", err)
	}
}
