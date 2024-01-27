<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\WordPressService;
use Illuminate\Http\Request;

class WordPressController extends Controller
{
    protected $wordpressService;

    public function __construct(WordPressService $wordpressService)
    {
        $this->wordpressService = $wordpressService;
    }

    public function index(): \Illuminate\View\View
    {
        return view('site.wordpress.index');
    }

    public function create(Request $request): \Illuminate\Http\RedirectResponse
    {
        // Valideer het binnenkomende verzoek
        $request->validate([
            'path'     => 'required|string', // Zorg ervoor dat dit path veilig is en correct gevalideerd wordt!
            'username' => 'required|string|min:3|max:255',
            'password' => 'required|string|min:8',
        ]);

        // Roep de WordPress service aan om de installatie uit te voeren
        $response = $this->wordpressService->deployWordPress(
            $request->input('path'),
            $request->input('username'),
            $request->input('password')
        );

        // Check de response van de service en reageer dienovereenkomstig
        if ($response['success']) {
            // Succes response - terugkeren naar een succespagina of een andere route
            return redirect()->route('some.route.name')->with('success', $response['message']);
        } else {
            // Fout response - terugkeren naar de oorspronkelijke pagina met foutgegevens
            return back()->withErrors(['Deployment failed' => $response['message']]);
        }
    }
}