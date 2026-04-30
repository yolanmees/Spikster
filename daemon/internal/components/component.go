package components

// Component defines the interface every installable unit must implement.
type Component interface {
	Name() string
	Install() error
	Repair() error
	Remove() error
	Healthcheck() (bool, string)
}
