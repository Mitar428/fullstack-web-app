class Radnik {
    private _ime: string;
    private _radnoVremeNedeljno: number;
    private _ukupnoOdradjenoSatiNedeljno: number;
    private _osnovica: number;
    private _koeficijentRada: number;

    constructor(ime: string, radnoVremeNedeljno: number, osnovica: number) {
        this._ime = ime;
        this._radnoVremeNedeljno = radnoVremeNedeljno;
        this._osnovica = osnovica;
        this._koeficijentRada = 1;
        this._ukupnoOdradjenoSatiNedeljno = 0;
    }

    public get ime(): string {
        return this._ime;
    }

    public set ime(value: string) {
        this._ime = value;
    }

    public get radnoVremeNedeljno(): number {
        return this._radnoVremeNedeljno;
    }

    public set radnoVremeNedeljno(value: number) {
        if (value >= 10) {
            this._radnoVremeNedeljno = value;
        }
    }

    public get ukupnoOdradjenoSatiNedeljno(): number {
        return this._ukupnoOdradjenoSatiNedeljno;
    }

    public set ukupnoOdradjenoSatiNedeljno(value: number) {
        if (value > 0) {
            this._ukupnoOdradjenoSatiNedeljno = value;
        }
    }

    public get osnovica(): number {
        return this._osnovica;
    }

    public set osnovica(value: number) {
        this._osnovica = value;
    }

    public get koeficijentRada(): number {
        return this._koeficijentRada;
    }

    public set koeficijentRada(value: number) {
        this._koeficijentRada = value;
    }

    povecajOdradjenoSati(sati: number): void {
        this._ukupnoOdradjenoSatiNedeljno += sati;
    }

    izracunajPlatu(): number {
        return this._osnovica * this._koeficijentRada;
    }

    preostaloSatiNedeljno(): number {
        return this._radnoVremeNedeljno - this._ukupnoOdradjenoSatiNedeljno;
    }

}

let r: Radnik = new Radnik("Pera Peric", 40, 600);
console.log(r.izracunajPlatu());
r.ime = "Marko";

if(r.ime != "Pera Peric"){
    console.log(r.ime);
} 